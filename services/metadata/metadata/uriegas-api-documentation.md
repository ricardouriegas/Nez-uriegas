# Uriegas FAIR Catalog Search API Documentation

## Requirements
- PHP 7.4 or higher
- PostgreSQL 12 or higher
<!-- DEPRECATED:
- Sqlmap (for sql injection) -->

## Endpoints

### Search Catalogs
```
GET /uriegas-search_catalogs.php
```

### Get Available Usernames
```
GET /uriegas-search_catalogs.php?action=get_usernames
```

**Optional Parameters:**
- `search`: Filter usernames containing this text (case insensitive)
- `limit`: Maximum number of usernames to return (default: 100, max: 100)

**Note:** To search by username, first call the `get_usernames` endpoint to retrieve a list of available usernames, then use the exact username in your search query. The username filter now requires exact matches only.

## Base URL

```
http://localhost:20505/uriegas-search_catalogs.php
```

## Request Parameters

### Required Parameters

| Parameter | Type | Description |
|-----------|------|-------------|
| `q` | string | Search term (minimum 2 characters). Use `*` for wildcard search to retrieve all catalogs. |

### Optional Parameters

| Parameter | Type | Description | Example Values |
|-----------|------|-------------|----------------|
| `privacy` | string | Filter by privacy status | `public`, `private` |
| `encryption` | string | Filter by encryption status | `true`, `false` |
| `processed` | string | Filter by processing status | `true`, `false` |
| `date_from` | string | Filter catalogs created from this date | `2024-01-01` |
| `date_to` | string | Filter catalogs created until this date | `2024-12-31` |
| `file_type` | string | Filter by file type (alphanumeric only) | `pdf`, `json`, `csv` |
| `username` | string | Filter by owner username (exact match) | `admin`, `user123` |
| `limit` | integer | Maximum number of results to return | `50`, `100`, `500` |
| `offset` | integer | Number of results to skip (for pagination) | `0`, `50`, `100` |
| `action` | string | Special action to perform | `get_usernames`, `rate_limit_status` |

### Pagination Parameters

The API now supports flexible pagination without hardcoded limits:

- **No Limits**: If `limit` is not specified, all matching results are returned
- **Custom Limits**: Specify any positive integer for `limit`
- **Offset Support**: Use `offset` for pagination (e.g., `offset=50&limit=50` for page 2)
- **Unlimited Results**: Removed the previous 100-result limitation

## Example Requests

### Basic Search
```bash
curl "http://localhost:20505/uriegas-search_catalogs.php?q=healthcare"
```

### Paginated Search
```bash
# Get first 25 results
curl "http://localhost:20505/uriegas-search_catalogs.php?q=healthcare&limit=25"

# Get next 25 results (page 2)
curl "http://localhost:20505/uriegas-search_catalogs.php?q=healthcare&limit=25&offset=25"

# Get all results (no limit)
curl "http://localhost:20505/uriegas-search_catalogs.php?q=healthcare"
```

### Advanced Search with Filters
```bash
curl "http://localhost:20505/uriegas-search_catalogs.php?q=*&privacy=public&encryption=false&processed=true&date_from=2024-01-01"
```

### Get Available Usernames
```bash
curl "http://localhost:20505/uriegas-search_catalogs.php?action=get_usernames"
```

### Search Usernames
```bash
curl "http://localhost:20505/uriegas-search_catalogs.php?action=get_usernames&search=admin&limit=50"
```

### Search by Username (Exact Match)
Note: Username search now requires exact match. Use the get_usernames endpoint to get a list of available usernames first.
```bash
curl "http://localhost:20505/uriegas-search_catalogs.php?q=*&username=admin"
```

## Response Format

### Success Response

```json
{
  "success": true,
  "fair_compliant": true,
  "search_metadata": {
    "search_term": "healthcare",
    "filters_applied": {
      "privacy": "public",
      "encryption": "false"
    },
    "timestamp": "2024-10-06T15:30:45+00:00",
    "total_results": 110,
    "returned_results": 50,
    "limited": true,
    "pagination": {
      "limit": 50,
      "offset": 0,
      "has_more": true
    }
  },
  "repository_statistics": {
    "total_catalogs": 150,
    "public_catalogs": 120,
    "private_catalogs": 30,
    "encrypted_catalogs": 75,
    "processed_catalogs": 140
  },
  "results": {
    "catalogs": [
      {
        "namecatalog": "Healthcare Data Collection 2024",
        "created_at": "2024-03-15T10:30:00Z",
        "owner_username": "dr_smith",
        "dispersemode": "replicated",
        "encryption": true,
        "isprivate": false,
        "processed": true,
        "file_count": 25,
        "group": "medical_research",
        "fair_findable": {
          "indexed": true,
          "searchable_fields": ["namecatalog"],
          "created_timestamp": "2024-03-15T10:30:00Z"
        },
        "fair_accessible": {
          "is_public": true,
          "access_protocol": "HTTP/HTTPS",
          "authentication_required": false
        },
        "fair_interoperable": {
          "format_standard": "PostgreSQL/JSON",
          "dispersal_method": "replicated",
          "encryption_standard": "ABE"
        },
        "fair_reusable": {
          "license": "institutional",
          "processing_status": "ready",
          "file_count": 25,
          "metadata_complete": true
        }
      }
    ]
  },
  "fair_principles": {
    "findable": "Rich metadata with searchable fields",
    "accessible": "Access control aware with user context",
    "interoperable": "Standardized JSON/PostgreSQL format",
    "reusable": "Complete provenance and processing metadata"
  }
}
```

### Get Usernames Response

```json
{
  "success": true,
  "usernames": [
    "admin",
    "dr_smith",
    "researcher01",
    "user123"
  ],
  "total_count": 4,
  "search_term": "",
  "limit_applied": 100
}
```

### Error Response

```json
{
  "success": false,
  "error": "Search term required (minimum 2 characters)",
  "fair_compliant": false
}
```

### Search Metadata Fields

The `search_metadata` object contains important information about the search results:

| Field | Type | Description |
|-------|------|-------------|
| `total_results` | integer | Total number of catalogs matching the search criteria |
| `returned_results` | integer | Number of catalogs returned in this response |
| `limited` | boolean | `true` if there are more results than returned (pagination available) |
| `search_term` | string | The search term used for the query |
| `filters_applied` | object | Applied filters (only non-empty filters included) |
| `timestamp` | string | ISO 8601 timestamp when the search was performed |
| `pagination` | object | Pagination information (when applicable) |

### Pagination Object Fields

| Field | Type | Description |
|-------|------|-------------|
| `limit` | integer/null | Maximum results requested (null if no limit) |
| `offset` | integer | Number of results skipped |
| `has_more` | boolean | `true` if more results are available beyond current page |

**Note:** When `limited` is `true` and `total_results` > `returned_results`, it means there are more catalogs available but the API limits results to 100 per request for performance.

## Catalog Object Properties

### Core Properties

| Property | Type | Description |
|----------|------|-------------|
| `namecatalog` | string | Human-readable catalog name |
| `created_at` | string | ISO 8601 timestamp of catalog creation |
| `owner_username` | string | Username of the catalog owner |
| `dispersemode` | string | Data distribution method (e.g., "replicated", "dispersed") |
| `encryption` | boolean | Whether the catalog is encrypted |
| `isprivate` | boolean | Whether the catalog is private |
| `processed` | boolean | Whether the catalog has been processed |
| `file_count` | integer | Number of files in the catalog |
| `group` | string | Group/category the catalog belongs to |

## Error Handling

### Common Error Codes

| Error Message | Cause | Solution |
|--------------|-------|----------|
| "Search term required (minimum 2 characters)" | Empty or too short search term | Provide a search term with at least 2 characters |
| "Only GET method allowed" | Using POST/PUT/DELETE | Use GET method only |
| "Database connection failed" | Database connectivity issues | Check database connectivity |

## Rate Limiting and DoS Protection

The API implements comprehensive DoS (Denial of Service) protection with the following limits:

### Rate Limits
- **30 requests per minute** per IP address
- **300 requests per hour** per IP address
- **Maximum request size**: 8KB
- **Minimum interval**: 1 second between successive requests

### Progressive Penalties
- **3 strikes**: 10-minute temporary ban
- **5 strikes**: 1-hour temporary ban  
- **10 strikes**: 24-hour temporary ban

### Violations That Trigger Penalties
- Exceeding rate limits (requests per minute/hour)
- Sending requests larger than 8KB
- Making requests faster than 1 second apart
- Multiple violations from the same IP

### Status Check Endpoint
```
GET /uriegas-search_catalogs.php?action=rate_limit_status
```

Returns current rate limit status for debugging:
```json
{
  "success": true,
  "rate_limit_status": {
    "ip": "192.168.1.100",
    "requests_last_minute": 5,
    "requests_last_hour": 45,
    "is_blacklisted": false,
    "strikes": 0
  },
  "limits": {
    "max_requests_per_minute": 30,
    "max_requests_per_hour": 300,
    "max_request_size_kb": 8
  },
  "protection_active": true
}
```

### Error Response for Rate Limiting
When rate limits are exceeded, the API returns HTTP 429:
```json
{
  "success": false,
  "error": "Request blocked: Too many requests per minute",
  "code": "DOS_PROTECTION_ACTIVE",
  "retry_after": 60
}
```

## Security Measures

The API implements input validation and sanitization to prevent SQL injection attacks was tested using sqlmap. For that testing you can run the following command (changing sqlmap's location) is:

```bash
~/.local/bin/sqlmap -u "http://localhost:20505/uriegas-search_catalogs.php?q=test" --batch --level=5 --risk=3
```
