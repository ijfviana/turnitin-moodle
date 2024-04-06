# turnitin-moodle

Conjunto de scripts que permiten interacciones con Turnintin para hacer tareas de tipo "bulk" que no se pueden hacer desde el frontend proporcionado por Turnitin.

Todos los sctipts se deben instalar en un carpeta denominada `cli` dentro de la carpeta de plugin de turnitin para moodle.


## 

Este script modifica la fecha de finalización de las clases que va creando turnitin. A cada clase se le asignará como fecha de finalización la fecha máxima entre las actividades asociadas a la clase y N meses desde la ejecución del script. Siempre se puede indicar por parámetros el número de meses (que por defecto es 4) y una fecha de finalización máxima

