-- Chapter 58: a member's loans and a book's loans are looked up by these columns. An index
-- finds the rows without reading the whole table; SQLite doesn't make one for a foreign key.
CREATE INDEX loans_by_member ON loans (member_id);
CREATE INDEX loans_by_book ON loans (book_id);
