import json
import math
import datetime
import mysql.connector
from flask import Flask, request, jsonify

app = Flask(__name__)

DB = {
    'host': '127.0.0.1',
    'user': 'root',
    'password': '',
    'database': 'civicportal'
}

# ── always return JSON, never Flask's default HTML error pages ──────────────
@app.errorhandler(400)
@app.errorhandler(404)
@app.errorhandler(405)
@app.errorhandler(500)
def json_error(e):
    return jsonify({'success': False, 'match': False, 'message': str(e)}), e.code


def db():
    return mysql.connector.connect(**DB)


def distance(a, b):
    return math.sqrt(sum((x - y) ** 2 for x, y in zip(a, b)))


# ── /enroll ─────────────────────────────────────────────────────────────────
@app.route('/enroll', methods=['POST'])
def enroll():
    body = request.get_json(force=True, silent=True) or {}
    uid  = body.get('user_id')
    desc = body.get('face_descriptor')

    if not uid or not desc:
        return jsonify({'success': False, 'message': 'Missing user_id or face_descriptor'}), 400

    try:
        conn   = db()
        cursor = conn.cursor()
        cursor.execute("SELECT id FROM face_data WHERE user_id = %s", (uid,))
        now = datetime.datetime.now()
        if cursor.fetchone():
            cursor.execute(
                "UPDATE face_data SET face_descriptor=%s, enrollment_date=%s, is_active=1 WHERE user_id=%s",
                (json.dumps(desc), now, uid)
            )
        else:
            cursor.execute(
                "INSERT INTO face_data (user_id, face_descriptor, enrollment_date, is_active) VALUES (%s,%s,%s,1)",
                (uid, json.dumps(desc), now)
            )
        conn.commit()
        cursor.close()
        conn.close()
        return jsonify({'success': True, 'message': 'Face enrolled successfully'})
    except Exception as exc:
        return jsonify({'success': False, 'message': str(exc)}), 500


# ── /verify ─────────────────────────────────────────────────────────────────
@app.route('/verify', methods=['POST'])
def verify():
    body        = request.get_json(force=True, silent=True) or {}
    uid         = body.get('user_id')
    input_desc  = body.get('face_descriptor')

    if not uid or not input_desc:
        return jsonify({'match': False, 'message': 'Missing user_id or face_descriptor'}), 400

    try:
        conn   = db()
        cursor = conn.cursor(dictionary=True)
        cursor.execute(
            "SELECT face_descriptor, confidence_threshold, failed_attempts, is_active "
            "FROM face_data WHERE user_id = %s",
            (uid,)
        )
        row = cursor.fetchone()

        if not row:
            cursor.close(); conn.close()
            return jsonify({'match': False, 'message': 'No face data registered for this account'}), 404

        if not row['is_active']:
            cursor.close(); conn.close()
            return jsonify({'match': False, 'message': 'Face login is disabled for this account'}), 403

        if row['failed_attempts'] >= 5:
            cursor.close(); conn.close()
            return jsonify({'match': False, 'message': 'Too many failed attempts — please log in with your password'}), 423

        stored    = json.loads(row['face_descriptor'])
        threshold = float(row['confidence_threshold'] or 0.5)
        dist      = distance(input_desc, stored)
        matched   = dist < threshold
        now       = datetime.datetime.now()

        if matched:
            cursor.execute(
                "UPDATE face_data SET last_verified_at=%s, verification_attempts=verification_attempts+1, failed_attempts=0 WHERE user_id=%s",
                (now, uid)
            )
        else:
            cursor.execute(
                "UPDATE face_data SET verification_attempts=verification_attempts+1, failed_attempts=failed_attempts+1 WHERE user_id=%s",
                (uid,)
            )
        conn.commit()
        cursor.close()
        conn.close()

        return jsonify({'match': matched, 'message': 'Match found' if matched else 'Face not recognized'})

    except Exception as exc:
        return jsonify({'match': False, 'message': str(exc)}), 500


# ── /disable ─────────────────────────────────────────────────────────────────
@app.route('/disable', methods=['POST'])
def disable():
    body = request.get_json(force=True, silent=True) or {}
    uid  = body.get('user_id')

    if not uid:
        return jsonify({'success': False, 'message': 'Missing user_id'}), 400

    try:
        conn   = db()
        cursor = conn.cursor()
        cursor.execute("UPDATE face_data SET is_active=0 WHERE user_id=%s", (uid,))
        conn.commit()
        cursor.close()
        conn.close()
        return jsonify({'success': True, 'message': 'Face login disabled'})
    except Exception as exc:
        return jsonify({'success': False, 'message': str(exc)}), 500


if __name__ == '__main__':
    app.run(port=5001, debug=False)
