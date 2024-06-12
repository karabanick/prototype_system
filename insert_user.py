import sqlite3

def connect_db():
    """
    Establish a connection to the SQLite database.
    """
    return sqlite3.connect('property_management.sqlite')

def insert_user(username, password):
    """
    Insert a new user into the Users table.
    
    Args:
        username (str): The username of the new user.
        password (str): The password of the new user.
    """
    try:
        conn = connect_db()
        cursor = conn.cursor()
        # Insert a new user into the Users table
        cursor.execute("INSERT INTO Users (username, password) VALUES (?, ?)", (username, password))
        conn.commit()
        print(f"User '{username}' added successfully.")
    except sqlite3.Error as e:
        print(f"An error occurred: {e}")
    finally:
        conn.close()

if __name__ == "__main__":
    # Define the new user details
    new_username = 'user505'
    new_password = 'password505'
    
    # Call the insert_user function to add the new user
    insert_user(new_username, new_password)

