import {StudentMap} from "./My.Import/StudentMap";
import {Student} from "./My.Import/Student";
export interface Import {
    students?: StudentMap
    student?: Student
}

import {Student} from "./My.Import/Student";
export interface MyMap extends Student {
}
