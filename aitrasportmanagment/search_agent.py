import json
import re
from typing import Any, Dict

from duckduckgo_search import DDGS

try:
    from ollama import Ollama
except ImportError:
    Ollama = None


def _normalize_snippets(results: Any) -> list[Dict[str, str]]:
    snippets = []

    for item in results:
        title = item.get('title', '') or item.get('heading', '') or ''
        body = item.get('body', '') or item.get('snippet', '') or item.get('text', '') or ''
        url = item.get('href', '') or item.get('url', '') or ''

        if title or body or url:
            snippets.append({
                'title': title.strip(),
                'snippet': body.strip(),
                'url': url.strip(),
            })

    return snippets


def _parse_response_text(text: str) -> Dict[str, Any]:
    trimmed = text.strip()
    match = re.search(r"\{.*\}", trimmed, flags=re.S)
    if not match:
        raise ValueError('Unable to extract JSON from model response.')

    json_text = match.group(0).replace("'", '"')
    data = json.loads(json_text)

    if 'price' not in data or not isinstance(data['price'], (int, float)):
        raise ValueError('Model response JSON does not contain a valid price.')

    return {
        'price': float(data['price']),
        'currency': data.get('currency', 'TND'),
        'source': data.get('source', 'internet'),
    }


def _query_qwen(prompt: str) -> str:
    if Ollama is not None:
        client = Ollama(base_url='http://localhost:11434')
        if hasattr(client, 'generate'):
            result = client.generate(model='qwen2.5', prompt=prompt, max_tokens=200)
            if isinstance(result, dict):
                return result.get('text', '') or json.dumps(result)
            return str(result)

        if hasattr(client, 'create'):
            result = client.create(model='qwen2.5', prompt=prompt, max_tokens=200)
            if isinstance(result, dict):
                return result.get('text', '') or json.dumps(result)
            return str(result)

        raise RuntimeError('Unsupported ollama client interface.')

    raise ImportError('The ollama library is required but not installed.')


def search_transport_price(route: str, transport_type: str) -> Dict[str, Any]:
    query_text = f'Current {transport_type} price for {route} 2026'

    with DDGS() as ddgs:
        search_results = list(ddgs.text(query_text, region='wt-wt', safesearch='Off', max_results=10))

    snippets = _normalize_snippets(search_results)
    if not snippets:
        raise RuntimeError('No search snippets were returned from DuckDuckGo.')

    prompt = (
        'Analyze these search snippets and extract the most realistic average price. '
        'Return ONLY a JSON object: {"price": float, "currency": "TND", "source": "internet"}.'
        '\n\nSearch snippets:\n'
        + json.dumps(snippets, ensure_ascii=False, indent=2)
    )

    response_text = _query_qwen(prompt)
    return _parse_response_text(response_text)
