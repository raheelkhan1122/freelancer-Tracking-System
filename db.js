const mysql = require('mysql2');

const pool = mysql.createPool({
  host: 'localhost',
  user: 'root',
  password: '',
  database: 'freelance_project_tracking',
  waitForConnections: true,
  connectionLimit: 10,
  queueLimit: 0
});

pool.getConnection((err, connection) => {
  if (err) {
    console.error('MySQL connection failed:', err.message);
    console.error('Make sure MySQL is running and the database exists.');
    return;
  }
  console.log('MySQL connected successfully');
  connection.release();
});

module.exports = pool;
