<?php

class ScrapeFile
{
    private $lines = [];

    public function counter($metricName, $counterValue, array $labels = []) {
        $fullMetricName = "energy_" . $metricName;
        $strLabels = "";
        $arrLabels = [];
        foreach($labels as $label => $value) {
            $arrLabels[] = sprintf("%s=\"%s\"", $label, $value);
        }
        if(count($arrLabels) > 0) {
            $strLabels = "{" . implode(",",$arrLabels) . "}";
        }

        array_push($this->lines,
            sprintf("# TYPE %s counter\n%s%s %.2f\n", $fullMetricName, $fullMetricName, $strLabels, $counterValue));
    }

    public function gauge($metricName, $counterValue, array $labels = []) {
        $fullMetricName = "energy_" . $metricName;
        $strLabels = "";
        $arrLabels = [];
        foreach($labels as $label => $value) {
            $arrLabels[] = sprintf("%s=\"%s\"", $label, $value);
        }
        if(count($arrLabels) > 0) {
            $strLabels = "{" . implode(",",$arrLabels) . "}";
        }

        array_push($this->lines,
            sprintf("# TYPE %s gauge\n%s%s %.2f\n", $fullMetricName, $fullMetricName, $strLabels, $counterValue));
    }

    public function printFile() {
        return implode("\n", $this->lines);
    }
}