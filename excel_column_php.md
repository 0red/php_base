Source: [https://gist.github.com/Ghostscypher/80d054bb96fcb9fa1d9668a6d9395ca7]

When working with excel and you need to know the column name given it's index for example column 0 is A, column 26 is AA
etc. Also given AA reverse the it to get a column index of 26.  A little bit into what each function does, `get_cell_value`
takes in a string of a given excel cell e.g. A5 where A is the column name and 0 is the row index. It returns the row and
column index as [5, 0] i.e. an array/tuple where 5 is the row index and 0 is the column index. In short row 5 column 0. The
`getColumnName` takes in the column index and reeturns the string version of it. For example `getColumnName(5)` means get
the string equivalent of column with a column index of 5 which returns E.

```php
<?php

function get_cell_value(string $data): array {
    // To ensure uniformity, convert all characters to upper case
    $data = strtoupper($data);

    // Splits string into individual character
    $data = str_split($data);

    // Default row and column index to indicate error
    $row_index = -1;
    $column_index = -1;

    // Dictionary that takes the alphabetical letter as it's ke
    $alphabet_reversed = [
        'A' => 0, 'B' => 1, 'C' => 2, 'D' => 3, 'E' => 4,
        'F' => 5, 'G' => 6, 'H' => 7, 'I' => 8, 'J' => 9, 
        'K' => 10, 'L' => 11, 'M' => 12, 'N' => 13, 'O' => 14,
        'P' => 15, 'Q' => 16, 'R' => 17, 'S' => 18, 'T' => 19,
        'U' => 20, 'V' => 21, 'W' => 22, 'X' => 23, 'Y' => 24, 'Z' => 25
    ];

    // Ensures that the row and column input have been correctly arranged
    // i.e. AA0 not A0A
    $started_row = false;
    
    // For each character in a string
    foreach($data as $char){
        if(is_numeric($char)){
            if($row_index == -1){
                $row_index = intval($char);
            } else {
                $row_index = ($row_index * 10) + intval($char);
            }
            
            $started_row = true;

        } else {
            if(isset($alphabet_reversed[$char])){
                if($column_index == -1){
                    $column_index = $alphabet_reversed[$char];
                } else {
                    $column_index = ($column_index * 26) + $alphabet_reversed[$char] + 26;
                }

                // Checks if there is an error in the arrangement of
                // letters e.g 4A instead of A4
                if($started_row){
                    $row_index = -1;
                    $column_index = -1;
                    break;
                }
            }
        }
    }

    // Return an array where the row_index is the first and column index is
    // is the second value
    // in python this is a tuple
    return [$row_index, $column_index];
}

// Gets column name as string from a given index e.g. index 0 gives A
function getColumnName(int $columnIndex): string {
    $columnIndex = abs($columnIndex);

    // When using position comment the line above and uncomment the one below
    // $columnIndex = abs($columnIndex) - 1;

    $alphabet = [
        'A', 'B', 'C', 'D', 'E', 'F', 'G', 'H', 'I', 'J', 'K', 'L', 
        'M', 'N', 'O', 'P', 'Q', 'R', 'S', 'T', 'U', 'V', 'W', 'X', 
        'Y', 'Z'
    ];
    
    // Recursive approach
    if($columnIndex >= 0 && $columnIndex < 26)
    {
        return $alphabet[$columnIndex];
    }
    else
    {
        // . is string conactenation in php
        // % is modulus
        return getColumnName(($columnIndex / 26) - 1) . getColumnName($columnIndex % 26);
    }
}

$column_value = 52;

print_r(get_cell_value(getColumnName($column_value) . "0"));

print_r(getColumnName($column_value));
```
