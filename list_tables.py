import sqlite3

def list_tables(conn):
    """
    List all tables in the database.
    
    Args:
        conn (sqlite3.Connection): SQLite connection object.

    Returns:
        list: A list of table names.
    """
    cursor = conn.cursor()
    cursor.execute("SELECT name FROM sqlite_master WHERE type='table';")
    tables = cursor.fetchall()
    return [table[0] for table in tables]

if __name__ == "__main__":
    # Connect to the database
    database = 'property_management.sqlite'
    conn = sqlite3.connect(database)

    # List all tables
    tables = list_tables(conn)
    print("Tables in the database:")
    for table in tables:
        print(table)

    # Close the database connection
    conn.close()
