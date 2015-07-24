<?php
/*********************************************************************************
 * TimeTrex is a Payroll and Time Management program developed by
 * TimeTrex Software Inc. Copyright (C) 2003 - 2014 TimeTrex Software Inc.
 *
 * This program is free software; you can redistribute it and/or modify it under
 * the terms of the GNU Affero General Public License version 3 as published by
 * the Free Software Foundation with the addition of the following permission
 * added to Section 15 as permitted in Section 7(a): FOR ANY PART OF THE COVERED
 * WORK IN WHICH THE COPYRIGHT IS OWNED BY TIMETREX, TIMETREX DISCLAIMS THE
 * WARRANTY OF NON INFRINGEMENT OF THIRD PARTY RIGHTS.
 *
 * This program is distributed in the hope that it will be useful, but WITHOUT
 * ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS
 * FOR A PARTICULAR PURPOSE.  See the GNU Affero General Public License for more
 * details.
 *
 * You should have received a copy of the GNU Affero General Public License along
 * with this program; if not, see http://www.gnu.org/licenses or write to the Free
 * Software Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA
 * 02110-1301 USA.
 *
 * You can contact TimeTrex headquarters at Unit 22 - 2475 Dobbin Rd. Suite
 * #292 Westbank, BC V4T 2E9, Canada or at email address info@timetrex.com.
 *
 * The interactive user interfaces in modified source and object code versions
 * of this program must display Appropriate Legal Notices, as required under
 * Section 5 of the GNU Affero General Public License version 3.
 *
 * In accordance with Section 7(b) of the GNU Affero General Public License
 * version 3, these Appropriate Legal Notices must retain the display of the
 * "Powered by TimeTrex" logo. If the display of the logo is not reasonably
 * feasible for technical reasons, the Appropriate Legal Notices must display
 * the words "Powered by TimeTrex".
 ********************************************************************************/


/**
 * @package GovernmentForms
 */
class GovernmentForms_Base {

	public $debug = FALSE;
	public $data = NULL; //Form data is stored here in an array.
	public $records = array(); //Store multiple records here to process on a single form. ie: T4's where two employees can be on a single page.
	public $records_total = array(); //Total for all records.

	public $class_directory = NULL;

	/*
	 * PDF related variables
	 */
	public $pdf_object = NULL;
	public $template_index = array();
	public $current_template_index = NULL;
	public $page_offsets = array( 0, 0 ); //x, y
	public $template_offsets = array( 0, 0 ); //x, y
	public $show_background = TRUE; //Shows the PDF background
	public $default_font = 'helvetica';


	function setDebug( $bool ) {
		$this->debug = $bool;
	}
	function getDebug() {
		return $this->debug;
	}

	function setClassDirectory( $dir ) {
		$this->class_directory = $dir;
	}
	function getClassDirectory() {
		return $this->class_directory;
	}

	function Output( $type ) {
		switch ( strtolower($type) ) {
			case 'pdf':
				return $this->_outputPDF( $type );
				break;
			case 'xml':
				return $this->_outputXML( $type );
				break;
			case 'efile':
				return $this->_outputEFILE( $type );
				break;
		}
	}

	function getRecords() {
		return $this->records;
	}
	function setRecords( $data ) {
		if ( is_array($data) ) {
			foreach( $data as $record ) {
				$this->addRecord( $record ); //Make sure preCalc() is called for each record.
			}
		} else {
			$this->records = $data;
		}
		return TRUE;
	}
	function addRecord( $data ) {
		//Filter functions should only be used for drawing the PDF, they do not modify the actual values themselves.
		//preCalc functions should be used to modify the actual values themselves, prior to drawing on the PDF, as well prior to totalling.
		//This is also important for calculating totals, so we can cap maximum contributions and such and get totals based on those properly.
		//preCalc functions can modify any other value in the record as well.
		if ( is_array( $data ) ) {
			if ( method_exists( $this, 'getPreCalcFunction' ) ) {
				foreach( $data as $key => $value ) {
					$filter_function = $this->getPreCalcFunction( $key );
					if ( $filter_function != '' ) {
						if ( !is_array( $filter_function ) ) {
							$filter_function = (array)$filter_function;
						}

						foreach( $filter_function as $function ) {
							//Call function
							if ( method_exists( $this, $function ) ) {
								$value = $this->$function( $value, $key, $data );
							}
						}
						unset($function);
					}

					$data[$key] = $value;
				}
			}

			$this->records[] = $data;
		}

		return TRUE;
	}
	function clearRecords() {
		$this->records = array();
	}
	function countRecords() {
		return count($this->records);
	}
	//Totals all the values for all the records.
	function sumRecords() {
		//Make sure we handle array elements with letters, so we can properly combine boxes with the same letters.
		$this->records_total = Misc::ArrayAssocSum( $this->records, NULL, NULL, TRUE );
		return TRUE;
	}
	function getRecordsTotal() {
		return $this->records_total;
	}

	/*
	 *
	 * Math functions
	 *
	 */
	function MoneyFormat($value, $pretty = TRUE) {
		if ( !is_numeric( $value ) ) {
			return FALSE;
		}
		if ( $pretty == TRUE ) {
			$thousand_sep = ',';
		} else {
			$thousand_sep = '';
		}

		return number_format( $value, 2, '.', $thousand_sep);
	}

	function getBeforeDecimal($float) {
		$float = $this->MoneyFormat( $float, FALSE );

		$float_array = preg_split('/\./', $float);

		if ( isset($float_array[0]) ) {
			return $float_array[0];
		}

		return FALSE;
	}

	function getAfterDecimal($float, $format_number = TRUE ) {
		if ( $format_number == TRUE ) {
			$float = $this->MoneyFormat( $float, FALSE );
		}

		$float_array = preg_split('/\./', $float);

		if ( isset($float_array[1]) ) {
			return str_pad($float_array[1], 2, '0');
		}

		return FALSE;
	}


	/*
	 *
	 * Date functions
	 *
	 */
	public function getYear($epoch = NULL) {
		if ($epoch == NULL) {
			$epoch = TTDate::getTime();
		}

		return date('Y', $epoch);
	}

	/*
	 *
	 * Validation functions
	 *
	 */
	function isNumeric( $value ) {
		if ( is_numeric( $value ) ) {
			return $value;
		}

		return FALSE;
	}

	/*
	 *
	 * Filter functions
	 *
	 */
	function stripSpaces($value) {
		return str_replace(' ', '', trim($value));
	}

	function stripNonNumeric($value) {
		$retval = preg_replace('/[^0-9]/', '', $value);

		return $retval;
	}

	function stripNonAlphaNumeric($value) {
		$retval = preg_replace('/[^A-Za-z0-9\ ]/', '', $value); //Don't strip spaces

		return $retval;
	}

	function stripNonFloat($value) {
		$retval = preg_replace('/[^-0-9\.]/', '', $value);

		return $retval;
	}

	/*
	 *
	 * EFILE (Fixed Length) Helper functions
	 *
	 */
	function removeDecimal( $value ) {
		$retval = str_replace('.', '', number_format( $value, 2, '.', '') );

		return $retval;
	}

	function padRecord( $value, $length, $type ) {
		$type = strtolower($type);

		//Trim record incase its too long.
		$value = substr( $value, 0, $length);

		switch ($type) {
			case 'n':
				$retval = str_pad( $value, $length, 0, STR_PAD_LEFT);
				break;
			case 'an':
				$retval = str_pad( $value, $length, ' ', STR_PAD_RIGHT);
				break;
		}

		return $retval;
	}

	function padLine( $line, $length = FALSE ) {
		if ( $line == '' ) {
			return FALSE;
		}

		$retval = str_pad( $line, ( $length == FALSE ) ? strlen($line) : $length, ' ', STR_PAD_RIGHT);

		return $retval."\r\n";
	}

	/*
	 *
	 * XML helper functions
	 *
	 */
	function setXMLObject( &$obj ) {
		$this->xml_object = $obj;
		return TRUE;
	}
	function getXMLObject() {
		return $this->xml_object;
	}

	/*
	 *
	 * PDF helper functions
	 *
	 */
	function setPDFObject( &$obj ) {
		$this->pdf_object = $obj;
		return TRUE;
	}
	function getPDFObject() {
		return $this->pdf_object;
	}

	function setShowBackground( $bool ) {
		$this->show_background = $bool;
		return TRUE;
	}
	function getShowBackground() {
		return $this->show_background;
	}

	function setPageOffsets( $x, $y ) {
		$this->page_offsets = array( $x, $y );
		return TRUE;
	}
	function getPageOffsets( $type = NULL ) {
		switch ( strtolower($type) ) {
			case 'x':
				return $this->page_offsets[0];
				break;
			case 'y':
				return $this->page_offsets[1];
				break;
			default:
				return $this->page_offsets;
				break;
		}
	}
	function setTemplateOffsets( $x, $y ) {
		$this->template_offsets = array( $x, $y );
		return TRUE;
	}
	function getTemplateOffsets( $type = NULL ) {
		switch ( strtolower($type) ) {
			case 'x':
				return $this->template_offsets[0];
				break;
			case 'y':
				return $this->template_offsets[1];
				break;
			default:
				return $this->template_offsets;
				break;
		}
	}

	function getTemplateDirectory() {
		$dir = $this->getClassDirectory() . DIRECTORY_SEPARATOR . 'templates';
		return $dir;
	}

	function getSchemaSpecificCoordinates( $schema, $key, $sub_key1 = NULL ) {

		unset($schema['function']);

		if ( $sub_key1 !== NULL ) {
			if ( isset($schema['coordinates'][$key][$sub_key1]) ) {
				return array( 'coordinates' => $schema['coordinates'][$key][$sub_key1] );
			}
		} else {
			if ( isset($schema['coordinates'][$key]) ) {
				return array( 'coordinates' => $schema['coordinates'][$key], 'font' => ( isset($schema['font']) ) ? $schema['font'] : array() );
			}
		}

		return FALSE;
	}

	//This gives the same affect of adding a new page on the next time Draw() is called.
	//Can be used when multiple records are processed for a single form.
	function resetTemplatePage() {
		$this->current_template_index = NULL;
		return TRUE;
	}

	//Draw all digits before the decimal in the first location, and after the decimal in the second location.
	function drawSplitDecimalFloat( $value, $schema) {

		if ( $value > 0 ) {
			$this->Draw( $this->getBeforeDecimal( $value ), $this->getSchemaSpecificCoordinates( $schema, 0 ) );
			$this->Draw( $this->getAfterDecimal( $value ), $this->getSchemaSpecificCoordinates( $schema, 1 ) );
		}

		return TRUE;
	}

	//Draw each char/digit one at a time in different locations.
	function drawChars( $value, $schema ) {
		$value = (string)$value; //convert integer to string.
		$max = strlen($value);
		for($i=0; $i < $max; $i++) {
			$this->Draw( $value[$i], $this->getSchemaSpecificCoordinates( $schema, $i ) );
		}

		return TRUE;
	}
    // Draw the same data at different locations
    // value should be string
    function drawPiecemeal( $value, $schema ) {
        unset( $schema['function'] );
        foreach( $schema['coordinates'] as $key => $coordinates ) {
            if ( is_array( $coordinates ) ) {
                if ( isset( $schema['font'] ) ) {
                    $this->Draw( $value, array( 'coordinates' => $coordinates, 'font' => $schema['font'] ) );
                } else {
                    $this->Draw( $value, array( 'coordinates' => $coordinates ) );
                }
            }
        }
        return TRUE;

    }

	//Draw each element of an array at different locations.
	//Value must be an array.
	function drawSegments( $value, $schema ) {

		if ( is_array($value) ) {
			$i=0;
			foreach( $value as $segment ) {
				$this->Draw( $segment, $this->getSchemaSpecificCoordinates( $schema, $i ) );
				$i++;
			}
		}

		return TRUE;
	}

	//Draw an X in each of the specified locations
	function drawSplitDecimalFloatGrid( $value, $schema ) {

		if ( !is_array( $value ) ) {
			$value = (array)$value;
		}

		foreach( $value as $key => $tmp_value ) {

			if ( $tmp_value !== FALSE ) {
				//var_dump($tmp_value, $schema['coordinates'][$key] );

				//$this->Draw( $this->getBeforeDecimal( $value ),  array('coordinates' => $schema['coordinates'][$key][0] ) );
				//var_dump( $this->getSchemaSpecificCoordinates( $schema, $key, 0 ) );
				//$this->Draw( $this->getBeforeDecimal( $value ), $this->getSchemaSpecificCoordinates( $schema, $key, 0 ) );

                if ( is_array($tmp_value) ) {

                    foreach( $tmp_value as $value ) {
                        $this->drawSplitDecimalFloat( $value, $this->getSchemaSpecificCoordinates( $schema, $key ) );
                    }
                } else {
                    $this->drawSplitDecimalFloat( $tmp_value, $this->getSchemaSpecificCoordinates( $schema, $key ) );
                }


				//$this->Draw( $tmp_value, $this->getSchemaSpecificCoordinates( $schema, $key ) );
			}
		}

		return TRUE;
	}

	//Draw an X in each of the specified locations
	//$value must be an array.
	function drawCheckBox( $value, $schema ) {
		$char = 'x';

		if ( !is_array( $value ) ) {
			$value = (array)$value;
		}

		foreach( $value as $tmp_value ) {
			//Skip any false values.
			if ( $tmp_value === FALSE ) {
				continue;
			}

			if ( is_string( $tmp_value ) ) {
				$tmp_value = strtolower($tmp_value);
			}

			if ( is_bool($tmp_value) AND $tmp_value == TRUE ) {
				$tmp_value = 0;
			}

			$this->Draw( $char, $this->getSchemaSpecificCoordinates( $schema, $tmp_value ) );
		}

		return TRUE;
	}

	function drawNormal( $value, $schema ) {
		if ( $value !== FALSE ) { //If value is FALSE don't draw anything, this prevents a blank cell from being drawn overtop of other text.
			unset($schema['function']); //Strip off the function element to prevent infinite loop
			$this->Draw( $value, $schema );
			return TRUE;
		}

		return FALSE;
	}

    function drawGrid( $value, $schema ) {

        unset($schema['function']);

        if ( isset( $schema['grid'] ) ) {
            $grid = $schema['grid'];
        }

        if ( is_array( $value ) ) {

			if ( isset( $grid ) AND is_array( $grid ) ) {

			     $top_left_x = $x = $grid['top_left_x'];
                 $top_left_y = $y = $grid['top_left_y'];
                 $h = $grid['h'];
                 $w = $grid['w'];
                 $step_x = $grid['step_x'];
                 $step_y = $grid['step_y'];
                 $col = $grid['column'];

			     $i=1;
                 foreach( $value as $val ) {

                    $coordinates = array(
                        'x' => $x,
                        'y' => $y,
                        'h' => $h,
                        'w' => $w,

                    );

                    $schema['coordinates'] = array_merge( $schema['coordinates'], $coordinates );

                    $this->Draw( $val, $schema );

                    if ( $i > 0 AND $i % $col == 0 ) {
                        $x = $top_left_x;
    					$y += $step_y;
    				} else {
    					$x += $step_x;
    				}
    				$i++;

                }

			}
        }

        return TRUE;
    }



	function addPage( $schema = NULL ) {
		$pdf = $this->getPDFObject();

		$pdf->AddPage();
		if ( $this->getShowBackground() == TRUE AND isset($this->template_index[$schema['template_page']]) ) {
			if ( isset($schema['combine_templates']) AND is_array($schema['combine_templates']) ) {
				$template_schema = $this->getTemplateSchema();

				//Handle combining multiple template together with a X,Y offset.
				foreach( $schema['combine_templates'] as $combine_template ) {
					//Debug::text('Combining Template Pages... Template: '. $combine_template['template_page'] .' Y: '. $combine_template['y'], __FILE__, __LINE__, __METHOD__, 10);
					$pdf->useTemplate( $this->template_index[$combine_template['template_page']], $combine_template['x']+$this->getTemplateOffsets('x'), $combine_template['y']+$this->getTemplateOffsets('y') );

					$this->setPageOffsets( $combine_template['x'], $combine_template['y']);
					$this->current_template_index = $schema['template_page'];
					$this->initPage( $template_schema );
				}
				unset($combine_templates);
				$this->setPageOffsets( 0, 0 ); //Reset page offsets after each template is initialized.
			} else {
				$pdf->useTemplate( $this->template_index[$schema['template_page']], $this->getTemplateOffsets('x'), $this->getTemplateOffsets('y') );
			}
		}
		$this->current_template_index = $schema['template_page'];


		return TRUE;
	}

	function initPage( $template_schema ) {
		if ( is_array($template_schema) ) {
			foreach( $template_schema as $field => $init_schema ) {
				if ( is_numeric($field) ) {
					//Debug::text(' Initializing Template Page... Field: '. $field, __FILE__, __LINE__, __METHOD__, 10);
					$this->Draw( $this->$field, $init_schema );
				}
			}
			unset($template_schema, $field, $init_schema);

			return TRUE;
		}

		return FALSE;
	}

	//Generic draw function that works strictly off the coordinate map.
	//It checks for a variable specific function before running though, so we can handle more complex
	//drawing functionality.
	function Draw( $value, $schema ) {
		if ( !is_array($schema) ) {
			return FALSE;
		}

		//If its set, use the static value from the schema.
		if ( isset($schema['value'])) {
			$value = $schema['value'];
			unset($schema['value']);
		}

		//If custom function is defined, pass off to that immediate.
		//Else, try the generic drawing method.
		if ( isset($schema['function'])  ) {
			if ( !is_array($schema['function']) ) {
				$schema['function'] = (array)$schema['function'];
			}
			foreach( $schema['function'] as $function ) {
				if ( method_exists( $this, $function) ) {
					$value = $this->$function($value, $schema);
				}
			}
			unset($function);

			return $value;
		}

		$pdf = $this->getPDFObject();

		//Make sure we don't load the same template more than once.
		if ( isset($schema['template_page']) AND $schema['template_page'] != $this->current_template_index ) {
			//Debug::text('Adding new page: '. $schema .' Template Page: '. $schema['template_page'], __FILE__, __LINE__, __METHOD__, 10);
			$this->addPage( $schema );
		} else {
			//Debug::text('Skipping template... Value: '. $value, __FILE__, __LINE__, __METHOD__, 10);
		}

		//on_background flag forces that item to only be shown if the background is as well.
		//This has to go below any addPage() call, otherwise pages won't be added if the first cell is only to be shown on the background.
		if ( isset($schema['on_background']) AND $schema['on_background'] == TRUE AND $this->getShowBackground() == FALSE ) {
			return FALSE;
		}

		if ( isset($schema['font']) ) {
			if ( !isset($schema['font']['font']) ) {
				$schema['font']['font'] = $this->default_font;
			}
			if ( !isset($schema['font']['type']) ) {
				$schema['font']['type'] = '';
			}
			if ( !isset($schema['font']['size']) ) {
				$schema['font']['size'] = 8;
			}

			$pdf->SetFont( $schema['font']['font'], $schema['font']['type'], $schema['font']['size']);
		} else {
			$pdf->SetFont( $this->default_font, '', 8 );
		}

		if ( isset($schema['coordinates']) ) {
			$coordinates = $schema['coordinates'];
			//var_dump( Debug::BackTrace() );

			if ( isset($coordinates['text_color']) AND is_array( $coordinates['text_color'] ) ) {
				$pdf->setTextColor( $coordinates['text_color'][0], $coordinates['text_color'][1], $coordinates['text_color'][2] );
			} else {
				$pdf->setTextColor( 0, 0, 0 ); //Black text.
			}

			if ( isset($coordinates['fill_color']) AND is_array( $coordinates['fill_color'] ) ) {
				$pdf->setFillColor( $coordinates['fill_color'][0], $coordinates['fill_color'][1], $coordinates['fill_color'][2] );
				$coordinates['fill'] = 1;
			} else {
				$pdf->setFillColor( 255, 255, 255 ); //White
				$coordinates['fill'] = 0;
			}

			$pdf->setXY( $coordinates['x']+$this->getPageOffsets('x'), $coordinates['y']+$this->getPageOffsets('y') );

			if ( $this->getDebug() == TRUE ) {
				$pdf->setDrawColor( 0, 0, 255 );
				$coordinates['border'] = 1;
			} else {
				if ( !isset($coordinates['border']) ) {
					$coordinates['border'] = 0;
				}
			}

			if ( isset($schema['multicell']) AND $schema['multicell'] == TRUE ) {
				//Debug::text('Drawing MultiCell... Value: '. $value, __FILE__, __LINE__, __METHOD__, 10);
				$pdf->MultiCell( $coordinates['w'], $coordinates['h'], $value, $coordinates['border'], strtoupper($coordinates['halign']), $coordinates['fill'] );
			} else {
				//Debug::text('Drawing Cell... Value: '. $value, __FILE__, __LINE__, __METHOD__, 10);
				$pdf->Cell( $coordinates['w'], $coordinates['h'], $value, $coordinates['border'], 0, strtoupper($coordinates['halign']), $coordinates['fill'] );
			}
			unset($coordinates);
		} else {
			Debug::text('NOT Drawing Cell... Value: '. $value, __FILE__, __LINE__, __METHOD__, 10);
		}

		return TRUE;
	}

	//Make sure we pass *ALL* data to this function, as it will overwrite existing data, but if one record has a field and another one doesn't,
	//we need to send blank fields so the data is overwritten correctly.
	function arrayToObject( $array ) {
		if ( is_array($array) ) {
			foreach( $array as $key => $value ) {
				$this->$key = $value;
			}
		}

		return TRUE;
	}

	/*
	 *
	 * Magic functions.
	 *
	 */
	function __set( $name, $value ) {
		$filter_function = $this->getFilterFunction( $name );
		if ( $filter_function != '' ) {
			if ( !is_array( $filter_function ) ) {
				$filter_function = (array)$filter_function;
			}

			foreach( $filter_function as $function ) {
				//Call function
				if ( method_exists( $this, $function ) ) {
					$value = $this->$function( $value );

					if ( $value === FALSE ) {
						return FALSE;
					}
				}
			}
			unset($function);
		}

		$this->data[$name] = $value;

		return TRUE;
	}

	function __get( $name ) {
		if ( isset($this->data[$name]) ) {
			return $this->data[$name];
		}

		return FALSE;
	}

    public function __isset($name) {
        return isset($this->data[$name]);
    }

    public function __unset($name) {
        unset($this->data[$name]);
    }
}
?>