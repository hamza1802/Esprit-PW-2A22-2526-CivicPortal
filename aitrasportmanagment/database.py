import os
import sqlite3
from datetime import datetime, timedelta

DB_PATH = os.path.join(os.path.dirname(__file__), 'price_cache.sqlite')

CREATE_TABLE_SQL = '''
CREATE TABLE IF NOT EXISTS price_cache (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    route TEXT NOT NULL,
    transport_type TEXT NOT NULL,
    price REAL NOT NULL,
    currency TEXT NOT NULL,
    source TEXT NOT NULL,
    timestamp TEXT NOT NULL,
    UNIQUE(route, transport_type)
);
'''


def get_connection():
    conn = sqlite3.connect(DB_PATH, detect_types=sqlite3.PARSE_DECLTYPES)
    conn.row_factory = sqlite3.Row
    return conn


def init_db() -> None:
    with get_connection() as conn:
        conn.execute(CREATE_TABLE_SQL)
        conn.commit()


def get_cached_price(route: str, transport_type: str, max_age_hours: int = 24):
    normalized_route = route.strip().lower()
    normalized_transport = transport_type.strip().lower()

    with get_connection() as conn:
        cursor = conn.execute(
            'SELECT route, transport_type, price, currency, source, timestamp FROM price_cache WHERE route = ? AND transport_type = ?',
            (normalized_route, normalized_transport),
        )
        row = cursor.fetchone()

    if row is None:
        return None

    timestamp = datetime.fromisoformat(row['timestamp'])
    if datetime.utcnow() - timestamp > timedelta(hours=max_age_hours):
        return None

    return {
        'route': row['route'],
        'transport_type': row['transport_type'],
        'price': float(row['price']),
        'currency': row['currency'],
        'source': row['source'],
        'timestamp': row['timestamp'],
    }


def set_cached_price(route: str, transport_type: str, price: float, currency: str = 'TND', source: str = 'internet'):
    normalized_route = route.strip().lower()
    normalized_transport = transport_type.strip().lower()
    timestamp = datetime.utcnow().isoformat()

    with get_connection() as conn:
        conn.execute(
            '''
            INSERT INTO price_cache (route, transport_type, price, currency, source, timestamp)
            VALUES (?, ?, ?, ?, ?, ?)
            ON CONFLICT(route, transport_type) DO UPDATE SET
                price = excluded.price,
                currency = excluded.currency,
                source = excluded.source,
                timestamp = excluded.timestamp
            ''',
            (normalized_route, normalized_transport, price, currency, source, timestamp),
        )
        conn.commit()

    return {
        'route': normalized_route,
        'transport_type': normalized_transport,
        'price': float(price),
        'currency': currency,
        'source': source,
        'timestamp': timestamp,
    }
