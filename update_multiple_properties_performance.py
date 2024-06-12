import sqlite3

def connect_db():
    """
    Establish a connection to the SQLite database.
    """
    return sqlite3.connect('property_management.sqlite')

def update_multiple_property_performance(property_ids, period, earnings, expenses_arrears, property_info, vacancy_status):
    """
    Update the performance metrics for multiple properties in the Property_Performance table.

    Args:
        property_ids (list of int): The IDs of the properties to update.
        period (int): The period for which the metrics are being updated.
        earnings (float): The earnings for the period.
        expenses_arrears (float): The expenses in arrears for the period.
        property_info (str): Information about the properties.
        vacancy_status (str): The vacancy status of the properties.
    """
    try:
        conn = connect_db()
        cursor = conn.cursor()
        # Iterate over property IDs and update performance metrics for each property
        for property_id in property_ids:
            cursor.execute("""
                UPDATE Property_Performance
                SET Earnings = ?, ExpensesArrears = ?, PropertyInfo = ?, VacancyStatus = ?
                WHERE PropertyID = ? AND PeriodID = ?
            """, (earnings, expenses_arrears, property_info, vacancy_status, property_id, period))
        conn.commit()
        print(f"Performance metrics updated successfully for {len(property_ids)} properties for Period {period}.")
    except sqlite3.Error as e:
        print(f"An error occurred: {e}")
    finally:
        conn.close()

if __name__ == "__main__":
    # Define the performance metrics for the properties update
    property_ids = [2, 3, 4]  # List of property IDs to update
    period = 2
    earnings = 190000
    expenses_arrears = 100000
    property_info = 'Recently renovated'
    vacancy_status = 'Open for conferences'
    
    # Call the update_multiple_property_performance function to update the metrics for multiple properties
    update_multiple_property_performance(property_ids, period, earnings, expenses_arrears, property_info, vacancy_status)
