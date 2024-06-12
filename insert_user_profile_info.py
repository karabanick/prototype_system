import sqlite3

def connect_db():
    """
    Establish a connection to the SQLite database.
    """
    return sqlite3.connect('property_management.sqlite')

def insert_user_profile(user_id, email, phone_number, address):
    """
    Insert user profile information into the Profiles table.
    """
    try:
        conn = connect_db()
        cursor = conn.cursor()
        cursor.execute("""
            INSERT INTO Profiles (UserID, Email, PhoneNumber, Address)
            VALUES (?, ?, ?, ?)
        """, (user_id, email, phone_number, address))
        conn.commit()
        print(f"Profile for user {user_id} inserted successfully.")
    except sqlite3.Error as e:
        print(f"An error occurred: {e}")
    finally:
        conn.close()

if __name__ == "__main__":
    # Example usage for inserting profile info for User101
    user_id = 2  # User101's UserID is 1
    insert_user_profile(user_id, 'user202@example.com', '+254 001001010', 'Kiambu Road')
