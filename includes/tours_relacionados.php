<?php
$toursRelacionadosIds = $tourActual['otros_tours'] ?? []; 

$toursRelacionadosData = [];
foreach ($toursRelacionadosIds as $relatedId) {
    foreach ($tours as $tour) {
        if ($tour['id'] === $relatedId) {
            $toursRelacionadosData[] = $tour;
            break;
        }
    }
} 
?>