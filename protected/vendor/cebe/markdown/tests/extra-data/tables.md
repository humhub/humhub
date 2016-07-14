Tables
------

First Header  | Second Header
------------- | -------------
Content Cell  | Content Cell
Content Cell  | Content Cell

| First Header  | Second Header |
| ------------- | ------------- |
| Content Cell  | Content Cell  |
| Content Cell  | Content Cell  |

| Name | Description          |
| ------------- | ----------- |
| Help      | Display the help window.|
| Close     | Closes a window     |

| Name | Description          |
| ------------- | ----------- |
| Help      | **Display the** help window.|
| Close     | _Closes_ a window     |

| Left-Aligned  | Center Aligned  | Right Aligned |
| :------------ |:---------------:| -----:|
| col 3 is      | some wordy text | $1600 |
| col 2 is      | centered        |   $12 |
| zebra stripes | are neat        |    $1 |


Simple | Table
------ | -----
1      | 2
3      | 4

| Simple | Table |
| ------ | ----- |
| 1      | 2     |
| 3      | 4     |
| 3      | 4     \|
| 3      | 4    \\|

Check https://github.com/erusev/parsedown/issues/184 for the following:

Foo | Bar | State
------ | ------ | -----
`Code | Pipe` | Broken | Blank
`Escaped Code \| Pipe` | Broken | Blank
Escaped \| Pipe | Broken | Blank
Escaped \\| Pipe | Broken | Blank
Escaped \\ | Pipe | Broken | Blank

| Simple | Table |
| :----- | ----- |
| 3      | 4     |
3      | 4
