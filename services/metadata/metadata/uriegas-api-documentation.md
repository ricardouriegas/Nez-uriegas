# Uriegas FAIR Catalog Search API Documentation

## Requirements
- PHP 7.4 or higher
- PostgreSQL 12 or higher
- Sqlmap (for sql injection)

## Endpoints

### Search Catalogs
```
GET /uriegas-search_catalogs.php
```

### Get Available Usernames
```
GET /uriegas-search_catalogs.php?action=get_usernames
```

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
| `user_id` | string | User token for access control and personalized results | `f3cde3d296c8e1bd...` |
| `privacy` | string | Filter by privacy status | `public`, `private` |
| `encryption` | string | Filter by encryption status | `true`, `false` |
| `processed` | string | Filter by processing status | `true`, `false` |
| `date_from` | string | Filter catalogs created from this date | `2024-01-01` |
| `date_to` | string | Filter catalogs created until this date | `2024-12-31` |
| `owner` | string | Filter by owner token (internal use) | `f3cde3d296c8e1bd...` |
| `file_type` | string | Filter by file type (alphanumeric only) | `pdf`, `json`, `csv` |
| `username` | string | Filter by owner username (exact match) | `admin`, `user123` |
| `action` | string | Special action to perform | `get_usernames` |

## Example Requests

### Basic Search
```bash
curl "http://localhost:20505/uriegas-search_catalogs.php?q=healthcare"
```

### Advanced Search with Filters
```bash
curl "http://localhost:20505/uriegas-search_catalogs.php?q=*&privacy=public&encryption=false&processed=true&date_from=2024-01-01"
```

### User-Specific Search
```bash
curl "http://localhost:20505/uriegas-search_catalogs.php?q=data&user_id=f3cde3d296c8e1bd2239f2772b3569a1483c496f977791644380f5f1463384ec"
```

### Get Available Usernames
```bash
curl "http://localhost:20505/uriegas-search_catalogs.php?action=get_usernames"
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
    "user_context": "f3cde3d296c8e1bd...",
    "filters_applied": {
      "privacy": "public",
      "encryption": "false"
    },
    "timestamp": "2024-10-06T15:30:45+00:00",
    "total_results": 5
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
  "total_count": 4
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

## Rate Limiting

- Results are limited to 100 catalogs per request
- No rate limiting is currently implemented on the endpoint

## Security Measures

The API implements input validation and sanitization to prevent SQL injection attacks, using sqlmap. For sql injection testing, you can run the following command (changing sqlmap's location) is:

```bash
~/.local/bin/sqlmap -u "http://localhost:20505/uriegas-search_catalogs.php?q=test" --batch --level=5 --risk=3
```
