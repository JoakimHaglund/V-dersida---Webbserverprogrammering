<?php
// enkel template klass
class Template
{
    // i nuläget är alla egenskaper private
    private $file; // för att ladda in templatfilen
    private $values = array(); // array för värden
    private $output; // spara innehåll för output

    // metod för att ladda templater
    public function load($file) {

	    $this->file = $file;

	    try {
	        if (!file_exists($this->file)) {
	            throw new Exception("Error loading template.");
	        } else {
	            $this->output .= file_get_contents($this->file);
	        }
	    } catch (Exception $e) {
	        echo $e;
	    }
	}

	// metod för att sätta värden till $values
	public function set($key, $value) {
	    $this->values[$key] = $value;
	}

	// går igenom $values och returnerar sedan den "färdiga" sidan i UTF-8
	public function render() {

		foreach($this->values as $key =>$val) {
		    $this->output = str_replace("[$key]", $val, $this->output);
		}

		return mb_convert_encoding($this->output, "UTF-8", "auto");
	}
}


