/**
 * Javascript Main File
 *
 * @author    Joshua Connor <connorj4@southernct.edu>
 * @copyright 2026 
 * @date      2026-02-24
 * @version   1.0
 * @param     none
 * @return    void
 */

// Output "program started" to the console
console.log("program started");

// go back to the prevous page
function goBack() {
  window.history.back();
}
/*
const users_01 =fetch('https://jsonplaceholder.typicode.com/users')
  .then(response => response.json())
  .then(data => console.log(data))
  .catch(error => console.log('Error:', error));

  console.table(users);
*/
async function fetchUsers() {
  try {
    const response = await fetch('https://jsonplaceholder.typicode.com/users');
    const data = await response.json();
    console.log(data);
    console.table(data);
  } catch (error) {
    console.log('Error:', error);
  }
};

fetchUsers();

// Output "completed" to the console
console.log("program completed");