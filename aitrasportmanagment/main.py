from datetime import datetime
from fastapi import FastAPI, HTTPException, Query
from pydantic import BaseModel, Field

import database as db
from search_agent import search_transport_price

app = FastAPI(
    title='Local Agentic Pricing Engine',
    description='A FastAPI service that uses local search and a local Qwen model to estimate transport prices in TND.',
    version='1.0.0',
)

VALID_TRANSPORT_TYPES = {'bus', 'train', 'ferry', 'flight'}

db.init_db()


class TicketPriceResponse(BaseModel):
    route: str = Field(..., example='Tunis to Sousse')
    transport_type: str = Field(..., example='bus')
    price: float = Field(..., example=12.5)
    currency: str = Field('TND', example='TND')
    source: str = Field(..., example='internet')
    cached: bool = Field(..., example=False)
    timestamp: str = Field(..., example='2026-05-04T12:34:56.789123')


@app.get('/get-ticket-price', response_model=TicketPriceResponse)
async def get_ticket_price(
    route: str = Query(..., description='Route description, e.g. "Tunis to Sousse"'),
    transport_type: str = Query(..., description='Transport type such as bus, train, ferry, or flight'),
):
    normalized_transport = transport_type.strip().lower()
    if normalized_transport not in VALID_TRANSPORT_TYPES:
        raise HTTPException(status_code=400, detail=f'Unsupported transport_type: {transport_type}')

    cached = db.get_cached_price(route, normalized_transport)
    if cached is not None:
        return TicketPriceResponse(
            route=route.strip(),
            transport_type=normalized_transport,
            price=cached['price'],
            currency=cached['currency'],
            source=cached['source'],
            cached=True,
            timestamp=cached['timestamp'],
        )

    try:
        fresh_price = search_transport_price(route, normalized_transport)
    except Exception as exc:
        raise HTTPException(status_code=500, detail=str(exc))

    cached_result = db.set_cached_price(
        route=route,
        transport_type=normalized_transport,
        price=fresh_price['price'],
        currency=fresh_price.get('currency', 'TND'),
        source=fresh_price.get('source', 'internet'),
    )

    return TicketPriceResponse(
        route=route.strip(),
        transport_type=normalized_transport,
        price=cached_result['price'],
        currency=cached_result['currency'],
        source=cached_result['source'],
        cached=False,
        timestamp=cached_result['timestamp'],
    )


@app.get('/')
async def root():
    return {'message': 'Local Agentic Pricing Engine is running. Use /get-ticket-price?route=...&transport_type=...'}
