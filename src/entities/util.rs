// First item is offset, second is the limit
pub struct Pagination (pub i64, pub i64);

impl Pagination {
    pub fn limit(limit: i64) -> Self {Pagination(0, limit)}
    pub fn offset(offset: i64) -> Self {Pagination(offset, -1)}
    pub fn new(offset: i64, limit: i64) -> Self {Pagination(offset, limit)}
}