import sqlite3

def connect_db():
    """
    Establish a connection to the SQLite database.
    """
    return sqlite3.connect('property_management.sqlite')

def update_user_profile(user_id, email=None, phone_number=None, address=None):
    """
    Update user profile information.
    """
    try:
        conn = connect_db()
        cursor = conn.cursor()
        update_query = "UPDATE Profiles SET"
        update_values = []
        if email is not None:
            update_query += " Email = ?,"
            update_values.append(email)
        if phone_number is not None:
            update_query += " PhoneNumber = ?,"
            update_values.append(phone_number)
        if address is not None:
            update_query += " Address = ?,"
            update_values.append(address)
        # Remove the trailing comma and space
        update_query = update_query.rstrip(",") 
        # Add WHERE clause to update only for the specified user
        update_query += " WHERE UserID = ?"
        update_values.append(user_id)
        
        cursor.execute(update_query, update_values)
        conn.commit()
        print(f"Profile for user {user_id} updated successfully.")
    except sqlite3.Error as e:
        print(f"An error occurred: {e}")
    finally:
        conn.close()

if __name__ == "__main__":
    # Example usage for updating profile info of User101
    user_id = 1  # User101's UserID is 1
    email = '101@email.com'
    phone_number = '+254 712345678'
    address = 'Nairobi - East'
    update_user_profile(user_id, email, phone_number, address)
