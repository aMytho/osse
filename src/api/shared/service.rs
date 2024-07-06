use diesel::{r2d2::{ConnectionManager, PooledConnection}, SqliteConnection};

pub trait DbConn {
    fn conn(&self) -> PooledConnection<ConnectionManager<SqliteConnection>>;
}
