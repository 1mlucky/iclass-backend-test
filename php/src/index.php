<?php
$servername = "127.0.0.1";
$port = "13306";
$username = "root";
$password = "verysecurerootpasswordiclassTECHtessolution12345672019docker";
$dbname = "employees";

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname, $port);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$sql = '
create unique index if not exists idx_salaries on salaries (emp_no, from_date);
create unique index if not exists idx_employees on employees (emp_no);
';

mysqli_query($conn, $sql);

// sql to create table
$sql = '

with
current_dept_manager(dept_no, emp_no, from_date)
as
(
    select dm2.dept_no as dept_no, dm2.emp_no as emp_no, dm1.from_date from
    (select dept_no, max(from_date) as from_date from dept_manager group by dept_no) as dm1
    left join
    dept_manager as dm2
    on dm1.dept_no = dm2.dept_no and dm1.from_date = dm2.from_date
),
current_employee_salaries(emp_no, salary)
as
(
    select s1.emp_no as emp_no, salary from
    (select emp_no, max(from_date) as from_date from salaries group by emp_no) as s1
    left join
    salaries as s2
    on s1.emp_no = s2.emp_no and s1.from_date = s2.from_date
),
employee_infos(emp_no, name, gender, serve_for)
as
(
    select emp_no, concat(first_name, " ", last_name) as name, gender, timestampdiff(year, hire_date, now()) as serve_for from employees
)
select
d.dept_name as dept_name,
e.name as name,
gender,
ms.salary as salary,
serve_for,
count(de.emp_no) as employees_count,
sum(es.salary) as employees_salary
from
departments as d
left join 
current_dept_manager as dm
using (dept_no)
left join
employee_infos as e
on dm.emp_no = e.emp_no
left join 
current_employee_salaries as ms
on dm.emp_no = ms.emp_no
left join 
current_dept_emp as de -- current_dept_emp include manager?
on dm.dept_no = de.dept_no
left join
current_employee_salaries as es
on de.emp_no = es.emp_no
group by dm.dept_no
order by dm.from_date asc;
';

$res = mysqli_query($conn, $sql);

if (mysqli_num_rows($res) > 0) {

    echo '<html>';
    echo '<link rel="stylesheet" type="text/css" href="./style.css">';

    echo '
    <table id="MyTable">
  <thead class="header">
    <tr class="header">
      <td class="header"><b>
          Department
        </b></td>
      <td class="header"><b>
          Name
        </b></td>
      <td class="header"><b>
          Salary
        </b></td>
      <td class="header"><b>
          Served for
        </b></td>
    </tr>
  </thead>
  <tbody>
  ';

    while ($row = mysqli_fetch_assoc($res)) {

        if ($row['gender'] == 'M') {
            echo "<tr class='male'>";
        } else {
            echo "<tr class='female'>";
        }

        echo "<td class='CellWithComment'>" . $row['dept_name'] .
            "<span class='CellComment'>" . $row['employees_count'] . " Employees under this Manager \$" . $row['employees_salary'] . " spent on them totally</span>" . "</td>";
        echo "<td class='CellWithComment'>" . $row['name'] . 
            "<span class='CellComment'>" . $row['employees_count'] . " Employees under this Manager \$" . $row['employees_salary'] . " spent on them totally</span>" . "</td>";
        echo "<td class='CellWithComment number'>" . $row['salary'] . 
            "<span class='CellComment'>" . $row['employees_count'] . " Employees under this Manager \$" . $row['employees_salary'] . " spent on them totally</span>" . "</td>";
        echo "<td class='CellWithComment number'>" . $row['serve_for'] . 
            "<span class='CellComment'>" . $row['employees_count'] . " Employees under this Manager \$" . $row['employees_salary'] . " spent on them totally</span>" . "</td>";
        echo "</tr>";
    }
    echo "</tbody>";
    echo "</table>";
} else {
    echo "<p>Error creating table:" . $conn->error . "</p>";
}

echo '</html>';

$conn->close();
?>