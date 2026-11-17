-- Chapter 69: tokens for the API. As for passwords, only a hash is kept: each token's SHA-256.
-- A token ends at its expiry date, or when its member is removed.
CREATE TABLE api_tokens (
    hash TEXT PRIMARY KEY,
    member_id INTEGER NOT NULL REFERENCES members (id) ON DELETE CASCADE,
    expires TEXT NOT NULL
) STRICT;
