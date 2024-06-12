import sqlite3

def connect_db():
    """
    Establish a connection to the SQLite database.
    """
    return sqlite3.connect('property_management.sqlite')

def update_user_password(user_id, new_password):
    """
    Update the password for an existing user.
    """
    try:
        conn = connect_db()
        cursor = conn.cursor()
        cursor.execute("""
            UPDATE Users SET Password = ? WHERE Username = ?
        """, (new_password, user_id))
        conn.commit()
        print(f"Password for user {user_id} updated successfully.")
    except sqlite3.Error as e:
        print(f"An error occurred: {e}")
    finally:
        conn.close()

if __name__ == "__main__":
    # Example usage
    user_id = 'User101'
    new_password = 'newpassword111'
    update_user_password(user_id, new_password)
