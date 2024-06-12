import sqlite3

def connect_to_database(database_name):
  conn = sqlite3.connect(database_name)
  return conn

def get_all_users(conn):
  cursor = conn.cursor()
  cursor.execute('''
                 SELECT * FROM Users
                 ''')
  return cursor.fetchall()

# def insert_user(conn, username, password):
  # cursor = conn.cursor()
  # cursor.execute('''
                 # INSERT INTO Users (username, password) VALUES (?, ?)
                 # ''', (username, password))
  # conn.commit()

# def update_property_performance(conn, property_id, period_id, earnings, expenses_arrears, property_info, vacancy_status):

  # profit = earnings - expenses_arrears

  # cursor = conn.cursor()
  # cursor.execute('''
                 # UPDATE Property_Performance
                 # SET Earnings = ?,
                 # ExpensesArrears = ?,
                 # Profit = ?,
                 # PropertyInfo = ?,
                 # VacancyStatus = ?
                 # WHERE PropertyId = ? AND PeriodId = ?
                 # ''', (earnings, expenses_arrears, profit, property_info, vacancy_status, property_id, period_id))
  conn.commit()

if __name__ == "__main__":

  conn = connect_to_database('property_management.sqlite')

  all_users = get_all_users(conn)
  print("All Users: ")
  for user in all_users:
    print(user)

  # update_property_performance(conn, property_id=1, period_id=1, earnings=30000, expenses_arrears=17000, property_info='Recently renovated', vacancy_status='Occupied')

  # update_property_performance(conn, property_id=2, period_id=2, earnings=40000, expenses_arrears=23000, property_info='Recently renovated', vacancy_status='Vacant')
  
  # update_property_performance(conn, property_id=3, period_id=3, earnings=100000, expenses_arrears=53000, property_info='Recently renovated', vacancy_status='Vacant')
  
  conn.close()
