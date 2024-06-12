import sqlite3

def connect_db():
    """
    Establish a connection to the SQLite database.
    """
    return sqlite3.connect('property_management.sqlite')

def update_property_performance(property_id, period, earnings, expenses_arrears, property_info, vacancy_status):
    """
    Update the performance metrics for a specific property in the Property_Performance table.

    Args:
        property_id (int): The ID of the property.
        period (int): The period for which the metrics are being updated.
        earnings (float): The earnings for the period.
        expenses_arrears (float): The expenses in arrears for the period.
        property_info (str): Information about the property.
        vacancy_status (str): The vacancy status of the property.
    """
    try:
        # Calculate profit
        profit = earnings - expenses_arrears

        conn = connect_db()
        cursor = conn.cursor()
        # Update the performance metrics including profit for the specified property and period
        cursor.execute("""
            UPDATE Property_Performance
            SET Earnings = ?, ExpensesArrears = ?, Profit = ?, propertyInfo = ?, VacancyStatus = ?
            WHERE PropertyID = ? AND PeriodID = ?
        """, (earnings, expenses_arrears, profit, property_info, vacancy_status, property_id, period))
        conn.commit()
        print(f"Performance metrics for Property ID {property_id} updated successfully for Period {period}.")
    except sqlite3.Error as e:
        print(f"An error occurred: {e}")
    finally:
        conn.close()

if __name__ == "__main__":
    # Define the performance metrics for the property update
    property_id = 1
    period = 1
    earnings = 50000
    expenses_arrears = 17000
    property_info = 'Recently renovated'
    vacancy_status = 'Pending renovation'
    
    # Call the update_property_performance function to update the metrics
    update_property_performance(property_id, period, earnings, expenses_arrears, property_info, vacancy_status)
