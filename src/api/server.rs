use poem::handler;

#[handler]
pub fn ping() -> &'static str {
    "hello"
}
