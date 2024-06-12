import sqlite3

def connect_db():
    """
    Establish a connection to the SQLite database.
    """
    return sqlite3.connect('property_management.sqlite')

def verify_user_credentials(user_id, user_password):
    """
    Verify user credentials.
    """
    try:
        conn = connect_db()
        cursor = conn.cursor()
        cursor.execute("""
            SELECT * FROM Users WHERE Username = ? AND Password = ?
        """, (user_id, user_password))
        user = cursor.fetchone()
        if user:
            print(f"User {user_id} authenticated successfully.")
            return True
        else:
            print(f"Authentication failed for user {user_id}.")
            return False
    except sqlite3.Error as e:
        print(f"An error occurred: {e}")
        return False
    finally:
        conn.close()

if __name__ == "__main__":
    # Example usage
    user_id = 'user606'
    user_password = 'password606'
    verify_user_credentials(user_id, user_password)
