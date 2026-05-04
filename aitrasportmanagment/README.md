# Local Agentic Pricing Engine

This project is a local transport pricing engine that uses:
- `FastAPI` for the HTTP API
- `duckduckgo-search` for free live internet search results
- `ollama` to query a local `qwen2.5` model on `http://localhost:11434`
- `SQLite` for caching recent route pricing

## Setup

1. Install dependencies:
   ```bash
   pip install -r requirements.txt
   ```

2. Pull the local model before starting:
   ```bash
   ollama pull qwen2.5
   ```

3. Start the service:
   ```bash
   uvicorn main:app --host 0.0.0.0 --port 8000 --reload
   ```

## Usage

Request a ticket price from the API:

```bash
curl "http://127.0.0.1:8000/get-ticket-price?route=Tunis%20to%20Sousse&transport_type=bus"
```

The service checks the local SQLite cache first. If an entry is older than 24 hours or missing, it performs a fresh web search, passes the snippets to the local Qwen model, and caches the resulting price.

## Notes

- This project is designed to run 100% locally.
- No external API keys are required.
- The local Ollama endpoint must be running at `http://localhost:11434`.
