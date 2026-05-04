---
name: lazyweb
description: Install and use Lazyweb MCP for AI-agent design research, UI references, screenshots, comparisons, and design feedback.
version: 1.0.0
tags:
  - design-research
  - ui-references
  - mcp
---

# Lazyweb

Use this skill when the user asks for UI inspiration, design research, app screenshots, product flow examples, onboarding patterns, pricing or paywall examples, competitive UI references, or feedback on an existing interface.

Lazyweb gives the agent access to real product screenshots and design patterns through the Lazyweb MCP server.

## Token Handling

Lazyweb MCP tokens are free no-billing bearer tokens for UI reference and design research tools. They do not authorize purchases, paid spend, private user data, or destructive actions. Avoid committing the token to public repos.

## Setup Verification

Verify by listing MCP tools, running `lazyweb_health`, and searching for `pricing page` with `lazyweb_search`.

## When To Use

- Before creating a landing page, app screen, onboarding flow, checkout, pricing page, dashboard, settings page, or mobile app UI.
- When asked to compare a design against real products.
- When asked to improve a screenshot or produce design recommendations.
- When a coding agent needs concrete UI examples instead of generic visual guesses.

## When Not To Use

- Backend-only tasks.
- Database schema work.
- Legal, medical, finance, or non-design research.
- Generic code cleanup with no UI or product-design component.

## Commands and Workflows

### Design Improve (/lazyweb-design-improve)
Use the `mcp_lazyweb_search` or specialized tools to find competitive references for the specific UI component being improved. Compare the current implementation (read via `read_file` for CSS/HTML) with the top-tier examples found.

1.  Identify the target component/page.
2.  Fetch local code (CSS/HTML).
3.  Search Lazyweb for similar high-quality examples.
4.  Provide a critique and specific improvement suggestions based on the differences.
