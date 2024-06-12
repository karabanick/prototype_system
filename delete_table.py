import sqlite3

def drop_table(conn, table_name):
    """
    Drop the specified table from the database.
    
    Args:
        conn (sqlite3.Connection): SQLite connection object.
        table_name (str): Name of the table to drop.
    """
    try:
        cursor = conn.cursor()
        cursor.execute(f"DROP TABLE IF EXISTS {table_name}")
        conn.commit()
        print(f"Table '{table_name}' has been deleted.")
    except sqlite3.Error as e:
        print(f"An error occurred: {e}")

if __name__ == "__main__":
    # Connect to the database
    database = 'property_management.sqlite'
    conn = sqlite3.connect(database)

    # List of duplicate tables to delete
    tables_to_delete = ['Location', 'Period', 'Properties_Performance']

    # Drop the duplicate tables
    for table in tables_to_delete:
        drop_table(conn, table)

    # Close the database connection
    conn.close()
