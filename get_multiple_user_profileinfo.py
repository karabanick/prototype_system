import sqlite3

def connect_db():
    """
    Establish a connection to the SQLite database.
    """
    return sqlite3.connect('property_management.sqlite')

def retrieve_all_user_profiles():
    """
    Retrieve all user profiles from the Profiles table.
    """
    try:
        conn = connect_db()
        cursor = conn.cursor()
        cursor.execute("SELECT UserID, Email, PhoneNumber, Address FROM Profiles")
        all_profiles = cursor.fetchall()
        for profile in all_profiles:
            user_id, email, phone_number, address = profile
            print(f"UserID: {user_id}, Email: {email}, Phone Number: {phone_number}, Address: {address}")
        return all_profiles
    except sqlite3.Error as e:
        print(f"An error occurred: {e}")
    finally:
        conn.close()

if __name__ == "__main__":
    # Example usage
    retrieve_all_user_profiles()
