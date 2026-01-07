<?php
// Debug script to check form submission structure
// Place this temporarily in store() method to see what data is being sent

// Add this after line 245 in AdminSuratController.php:
// dd($data); // Uncomment to see the structure

// Expected structure for table with pemohon column:
// $data['table_field_key'] = [
//     0 => [
//         'nama_pemohon' => [
//             'type' => 'mahasiswa',
//             'id' => 123
//         ],
//         'other_column' => 'some value'
//     ],
//     1 => [
//         'nama_pemohon' => [
//             'type' => 'dosen',
//             'id' => 456
//         ],
//         'other_column' => 'another value'
//     ]
// ];

echo "Debug file created. This explains the expected data structure.\n";
echo "To debug, add dd(\$data); after line 245 in AdminSuratController.php\n";
