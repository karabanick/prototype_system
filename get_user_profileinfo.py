import sqlite3

def connect_db():
    """
    Establish a connection to the SQLite database.
    """
    return sqlite3.connect('property_management.sqlite')

def get_user_profile(user_id):
    """
    Retrieve user profile information.
    """
    try:
        conn = connect_db()
        cursor = conn.cursor()
        cursor.execute("""
            SELECT Email, PhoneNumber, Address
            FROM Profiles
            WHERE UserID = ?
        """, (user_id,))
        profile_info = cursor.fetchone()
        if profile_info:
            email, phone_number, address = profile_info
            print(f"User Profile Information for {user_id}:")
            print(f"Email: {email}")
            print(f"Phone Number: {phone_number}")
            print(f"Address: {address}")
            return profile_info
        else:
            print(f"No profile found for user {user_id}.")
            return None
    except sqlite3.Error as e:
        print(f"An error occurred: {e}")
    finally:
        conn.close()

if __name__ == "__main__":
    # Example usage
    user_id = '1'
    get_user_profile(user_id)
