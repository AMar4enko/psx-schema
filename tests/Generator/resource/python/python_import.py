from student_map import StudentMap
from student import Student
@dataclass
class Import:
    students: StudentMap
    student: Student

from student import Student
@dataclass
class MyMap(Student):
