-- Chapter 61: a member may have a password. The column holds its hash, made by password_hash,
-- never the password itself. NULL: the member has no password yet, and can't log in.
ALTER TABLE members ADD COLUMN password_hash TEXT;
