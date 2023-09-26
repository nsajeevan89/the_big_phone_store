<?php

function parseCSVAndGenerateCombinations($inputFile, $outputFile) {
    // Read the input CSV file
    $csvData = array_map('str_getcsv', file($inputFile));

    // Extract the column headers
    $headers = array_shift($csvData);
    //Add count header for the count cell
    $headers=[...$headers,'count'];


    $inputArray = [];
    // Getting the keys of the item
     $keys = array_keys($csvData[0]);
     // Finding the last key of the item
     $lastKey = end($keys);
     // Key for count value
     $countKey = $lastKey + 1;

    foreach ($csvData as $item) {
        // Serialize the subarray as a string to use as a key for grouping
        $key = serialize(array_slice($item, 0));
        
        // If the key already exists in the result array, increment the count
        if (array_key_exists($key, $inputArray)) {
            $inputArray[$key][$countKey]++;
        } else {
            // Otherwise, create a new entry with count as 1
            $item[$countKey] = 1;
            $inputArray[$key] = $item;
        }
    }
    
    // Convert the result array back to a numeric-indexed array
    $inputArray = array_values($inputArray);
    $inputArray = [$headers,...$inputArray];
    
    // Check the type of output file format
    $fileFormat = pathinfo($outputFile, PATHINFO_EXTENSION);

    //if the file want to excutive as CSV format
    If($fileFormat==="csv"){
        // Write the unique combinations to the output CSV file
        $uniqueCombinations = fopen($outputFile, 'w');

        // Loop through the array and write each row to the CSV file
        foreach ($inputArray as $row) {
            fputcsv($uniqueCombinations, $row);
        }

        echo "Unique combinations have been written to $outputFile\n";
    }elseif($fileFormat==="json"){
        // Extract the header row
        $headers = $inputArray[0];
        // Initialize an array to store the transformed data
        $jsonData = [];

        // Loop through the data starting from the second row
        for ($i = 1; $i < count($inputArray); $i++) {
            // Create an associative array by mapping the header keys to the current row values
            $item = array_combine($headers, $inputArray[$i]);
            
            // Convert the count value to a string
            $item["count"] = strval($item["count"]);
            
            // Add the associative array to the $jsonData array
            $jsonData[] = $item;
        }

        // Convert the associative array to JSON format
        $jsonString = json_encode($jsonData);

        // Output the JSON data
        header('Content-Type: application/json');
        
        file_put_contents($outputFile, $jsonString);
        echo "Unique combinations have been written to $outputFile\n";
    }elseif($fileFormat==="xml"){
        // Create a root element for the XML
        $xml = new SimpleXMLElement('<root/>');

        // Loop through the data starting from the second row
        for ($i = 1; $i < count($inputArray); $i++) {
            // Create an item element for each row
            $item = $xml->addChild('item');
            
            // Loop through the columns and add them as child elements
            for ($j = 0; $j < count($inputArray[0]); $j++) {
                $item->addChild($inputArray[0][$j], $inputArray[$i][$j]);
            }
        }

        // Convert the XML object to a string
        $xmlString = $xml->asXML();

        file_put_contents($outputFile, $xmlString);
        echo "Unique combinations have been written to $outputFile\n";
    }else{
        echo "Output fileformat is not suppotinng...";
    }
}

// Check if command-line arguments are provided
if ($argc < 4) {
    echo "Usage: php parser.php --file input.csv --unique-combinations=output.csv\n";
    exit(1);
}

// Parse command-line arguments
$options = getopt('null', ["file:", "unique-combinations:"]);

// Get input and output file paths from command-line arguments
$inputFile = $options['file'];
$outputFile = $options['unique-combinations'];

// Read CSV file
$csvFile = fopen($inputFile, 'r');

//Check if input file exist or not
if($csvFile !== false){
    //Check if the output file name already exist or not
    if(!file_exists($outputFile)){
        // Call the function to parse the CSV and generate unique combinations
        parseCSVAndGenerateCombinations($inputFile, $outputFile);
    }else{
        echo "The output file name already exists.\n";
    }

}else{
    echo "Error opening the CSV file.\n";
}


?>
