import mysql.connector
from flask import Flask, request, jsonify
import json
import math
import datetime

app = Flask(__name__)

db_config = {
    'host': '127.0.0.1',
    'user': 'root',
    'password': '',
    'database': 'civic'
}

def get_db_connection():
    return mysql.connector.connect(**db_config)

def euclidean_distance(v1, v2):
    return math.sqrt(sum((a - b) ** 2 for a, b in zip(v1, v2)))

@app.route('/enroll', methods=['POST'])
def enroll():
    data = request.json
    user_id = data.get('user_id')
    face_descriptor = data.get('face_descriptor')
    
    if not user_id or not face_descriptor:
        return jsonify({'success': False, 'message': 'Missing user_id or face_descriptor'}), 400
    
    try:
        conn = get_db_connection()
        cursor = conn.cursor()
        
        # Check if already exists
        cursor.execute("SELECT id FROM face_data WHERE user_id = %s", (user_id,))
        existing = cursor.fetchone()
        
        if existing:
            cursor.execute(
                "UPDATE face_data SET face_descriptor = %s, enrollment_date = %s, is_active = 1 WHERE user_id = %s",
                (json.dumps(face_descriptor), datetime.datetime.now(), user_id)
            )
        else:
            cursor.execute(
                "INSERT INTO face_data (user_id, face_descriptor, enrollment_date, is_active) VALUES (%s, %s, %s, 1)",
                (user_id, json.dumps(face_descriptor), datetime.datetime.now())
            )
        
        conn.commit()
        cursor.close()
        conn.close()
        return jsonify({'success': True, 'message': 'Face enrolled successfully'})
    except Exception as e:
        return jsonify({'success': False, 'message': str(e)}), 500

@app.route('/verify', methods=['POST'])
def verify():
    data = request.json
    user_id = data.get('user_id')
    input_descriptor = data.get('face_descriptor')
    
    if not user_id or not input_descriptor:
        return jsonify({'match': False, 'message': 'Missing user_id or face_descriptor'}), 400
    
    try:
        conn = get_db_connection()
        cursor = conn.cursor(dictionary=True)
        
        cursor.execute("SELECT face_descriptor, confidence_threshold, failed_attempts, is_active FROM face_data WHERE user_id = %s", (user_id,))
        row = cursor.fetchone()
        
        if not row:
            return jsonify({'match': False, 'message': 'Face data not found for user'}), 404
        
        if not row['is_active']:
            return jsonify({'match': False, 'message': 'Face login is disabled for this user'}), 403

        if row['failed_attempts'] >= 5:
             return jsonify({'match': False, 'message': 'Account locked due to too many failed attempts'}), 423
        
        stored_descriptor = json.loads(row['face_descriptor'])
        distance = euclidean_distance(input_descriptor, stored_descriptor)
        threshold = row['confidence_threshold']
        
        match = distance < threshold
        
        now = datetime.datetime.now()
        if match:
            cursor.execute(
                "UPDATE face_data SET last_verified_at = %s, verification_attempts = verification_attempts + 1, failed_attempts = 0 WHERE user_id = %s",
                (now, user_id)
            )
        else:
            cursor.execute(
                "UPDATE face_data SET verification_attempts = verification_attempts + 1, failed_attempts = failed_attempts + 1 WHERE user_id = %s",
                (user_id,)
            )
            
        conn.commit()
        cursor.close()
        conn.close()
        
        return jsonify({
            'match': match,
            'confidence': 1 - distance,
            'message': 'Match found' if match else 'Match failed'
        })
    except Exception as e:
        return jsonify({'match': False, 'message': str(e)}), 500

@app.route('/disable', methods=['POST'])
def disable():
    data = request.json
    user_id = data.get('user_id')
    
    if not user_id:
        return jsonify({'success': False, 'message': 'Missing user_id'}), 400
        
    try:
        conn = get_db_connection()
        cursor = conn.cursor()
        cursor.execute("UPDATE face_data SET is_active = 0 WHERE user_id = %s", (user_id,))
        conn.commit()
        cursor.close()
        conn.close()
        return jsonify({'success': True, 'message': 'Face login disabled'})
    except Exception as e:
        return jsonify({'success': False, 'message': str(e)}), 500

if __name__ == '__main__':
    app.run(port=5001)
