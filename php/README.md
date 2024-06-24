# Raw PHP and HTML/CSS/JS

## Instructions

- You will also have to wrap the PHP script and other resources into a Docker image and add it as a service into `docker-compose.yml` as well.

- You should expose a port to make the server accessible at `localhost:28081`. Note that you can access the MySQL instance at `mysql:3306` from inside your container.

The required information returned from the SQL query, [sorted] ascendingly by the time they start to supervise the department, 

# finalized sql

```sql
set profiling = 1;
create unique index if not exists idx_salaries on salaries (emp_no, from_date);
create unique index if not exists idx_employees on employees (emp_no);
-- create unique index if not exists idx_current_dept_emp on current_dept_emp (dept_no);

-- alter table employees add unique index idx_employees (emp_no) if not exists;

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
order by dm.from_date asc
show profiles;
```

And then, you will have to use HTML/CSS/JS to visualize the data. The requirement include:

1. A table that display the department, name, salary and the number of years they have solved for for these managers.

2. Columns in the table in (1) will have to be equally splitted, the table's width should fit exactly the screen size.

3. All columns that display number should be aligned to right.

4. All rows for male manager should be colored in blue, while those of female manager should be colored in red.

5. Text in the table should be colored white.

6. A popup box displaying the number of employees in such department and their total salary when the row is hovered.

![demo](https://i.ibb.co/Vtj3PJK/Screenshot-from-2019-06-26-16-35-49.png)

## Requirements

1. You cannot add any extra files other than the existing `index.php`, `script.js` and `style.css`

2. Put PHP scripts and HTML markups in `.php` file, and other resources in corresponding file.

3. No Bootstrap, no jQuery. You only have RAW CSS and JS for client-side.

4. You only have `mysqli` plugin for server-side.

5. You cannot further process the data using PHP, you have to query everything directly using 1 MySQL query only.

6. Query performance is not in concern. Anything that takes less than 1 minute to run is accepted.

7. You cannot put styles into HTML. You can only use CSS classes.

Good luck writing the SQL query. I know it will be hard. My solution is 56 lines after formatting in PHPMyAdmin.

## Sample output

Assuming that my sql query is correct, you should get the following result after running your query. Note that the value for `serve_for` may differ depending on current date.

```
dept_name            name                gender    salary    serve_for     employees_count  employees_salary
Finance              Isamu Legleitner    F         83457     34            12437            977049936
Sales                Hauke Zhang         M         101987    32            37701            3349845802
Research             Hilary Kambil       F         79393     31            15441            1048650423
Marketing            Vishwani Minakawa   M         106491    33            14842            1188233434
Human Resources      Karsten Sigstam     F         65400     34            12898            824464664
Development          Leon DasSarma       F         74510     33            61386            4153249050
Quality Management   Dung Pesch          M         72876     30            14546            951919236
Customer Service     Yuchang Weedman     M         58745     30            17569            1182134209
Production           Oscar Ghazalie      M         56654     27            53304            3616319369

```
