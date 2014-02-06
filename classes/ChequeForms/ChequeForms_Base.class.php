<?php
/*********************************************************************************
 * TimeTrex is a Payroll and Time Management program developed by
 * TimeTrex Software Inc. Copyright (C) 2003 - 2013 TimeTrex Software Inc.
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
/*
 * $Revision: 2286 $
 * $Id: CA.class.php 2286 2008-12-12 23:12:41Z ipso $
 * $Date: 2008-12-12 15:12:41 -0800 (Fri, 12 Dec 2008) $
 */

/**
 * @package ChequeForms
 */
class ChequeForms_Base {

	public $debug = FALSE;
	public $data = NULL; //Form data is stored here in an array.
	public $records = array(); //Store multiple records here to process on a single form. ie: T4's where two employees can be on a single page.

	public $class_directory = NULL;

	/*
	 * PDF related variables
	 */
	public $pdf_object = NULL;
	public $template_index = array();
	public $current_template_index = NULL;
	public $page_offsets = array( 0, 0 ); //x, y
	public $template_offsets = array( 0, 0 ); //x, y
	public $show_background = FALSE; //Do not show the PDF background
	public $default_font = 'helvetica'; // helvetica

	function setDebug( $bool ) {
		$this->debug = $bool;
	}
	function getDebug() {
		return $this->debug;
	}

	function setClassDirectory( $dir ) {
		$this->class_directory = $dir;
	}

	function Output( $type ) {
		switch ( strtolower($type) ) {
			case 'pdf':
				return $this->_outputPDF( $type );
				break;
			case 'xml':
				return $this->_outputXML( $type );
				break;
		}
	}

	function getRecords() {
		return $this->records;
	}
	function setRecords( $data ) {
		$this->records = $data;
		return TRUE;
	}
	function addRecord( $data ) {
		$this->records[] = $data;
		return TRUE;
	}
	function clearRecords() {
		$this->records = array();
	}
	function countRecords() {
		return count($this->records);
	}
    function getFilterFunction( $name ) {
		$variable_function_map = array();

		if ( isset($variable_function_map[$name]) ) {
			return $variable_function_map[$name];
		}

		return FALSE;
	}

    /*
    *
    *
    * Automatically calculate the amount_words
    *
    */
    function filterAmountWords( $value ) {

        if ( isset( $this->amount ) ) {
            $numbers_words = new Numbers_Words();
            $value =  str_pad( ucwords( $numbers_words->toWords( floor($this->amount),"en_US") ).' ', 65, "-", STR_PAD_RIGHT );
        }
        return $value;
    }
    /*
    *
    *
    * Automatically calculate the amount_cents
    *
    */
    function filterAmountCents( $value ) {
        if ( isset( $this->amount ) ) {
            $value = Misc::getAfterDecimal($this->amount) .'/100';
        }
        return $value;
    }
    function filterAmountWordsCents( $value ) {
        return $this->filterAmountWords($value) . TTi18n::gettext(' and ') . $this->filterAmountCents($value) .  ' *****';
    }
    /*
    *
    *
    * Automatically calculate the amount_padded
    *
    */
    function filterAmountPadded( $value ) {
        if ( isset( $this->amount ) ) {
            $value = str_pad( Misc::MoneyFormat( $this->amount ),12,'*', STR_PAD_LEFT);
        }
        if ( get_class($this) === 'ChequeForms_9085' ) {
            return ' ' . $this->symbol . $value;
        }
        if ( get_class($this) === 'ChequeForms_FORM1' ) {
            return '  ' . $this->symbol . $value;
        }
        if ( get_class($this) === 'ChequeForms_FORM2' ) {
            return $this->symbol . '  ' . $value;
        }
        return $value;
    }
    // Format date as the country

    function filterDate( $epoch ) {
        if ( isset( $this->country ) AND $this->country == 'CA' ) {
			$date_format = 'd/m/Y';
		} else {
			$date_format = 'm/d/Y';
		}

        return date( $date_format, $epoch );

    }
    function filterAddress( $value ) {
		if ( isset( $this->address1 ) ) {
		   $value = $this->address1 . ' ';
		}
        if ( isset( $this->address2 ) ) {
            $value .= $this->address2;
        }

		return $value;
    }
    function filterProvince( $value ) {
        if ( isset( $this->city ) ) {
            $value = $this->city;
        }
        if ( isset( $this->province ) ) {
            $value .= ', ' . $this->province;
        }
        if ( isset( $this->postal_code ) ) {
            $value .= ' ' . $this->postal_code;
        }

        return $value;
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


	//This gives the same affect of adding a new page on the next time Draw() is called.
	//Can be used when multiple records are processed for a single form.
	function resetTemplatePage() {
		$this->current_template_index = NULL;
		return TRUE;
	}
    function getSchemaSpecificCoordinates( $schema, $key, $sub_key1 = NULL ) {

		unset($schema['function']);

		if ( $sub_key1 !== NULL ) {
			if ( isset($schema['coordinates'][$key][$sub_key1]) ) {
				return array( 'coordinates' => $schema['coordinates'][$key][$sub_key1] );
			}
		} else {
			if ( isset($schema['coordinates'][$key]) ) {
				/*
				$tmp = $schema['coordinates'][$key];
				unset($schema['coordinates']);
				$schema['coordinates'] = $tmp;

				return $schema;
				*/
				return array( 'coordinates' => $schema['coordinates'][$key], 'font' => $schema['font'] );
			}
		}

		return FALSE;
	}

    // Draw the same data at different locations
    // value should be string
    function drawPiecemeal( $value, $schema ) {
        unset( $schema['function'] );
        foreach( $schema['coordinates'] as $key => $coordinates ) {
            if ( is_array( $coordinates ) ) {

                $mode['coordinates'] = $coordinates;
                if ( isset( $schema['font'] ) ) {
                    $mode['font'] = $schema['font'];
                }
                if ( isset( $schema['multicell'] ) ) {
                    $mode['multicell'] = $schema['multicell'];
                }

                $this->Draw( $value, $mode );
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

	function drawNormal( $value, $schema ) {
		if ( $value !== FALSE ) { //If value is FALSE don't draw anything, this prevents a blank cell from being drawn overtop of other text.
			unset($schema['function']); //Strip off the function element to prevent infinite loop
			$this->Draw( $value, $schema );
			return TRUE;
		}

		return FALSE;
	}

	function addPage( $schema = NULL ) {
		$pdf = $this->getPDFObject();

		$pdf->AddPage();
		if ( $this->getShowBackground() == TRUE AND isset($this->template_index[$schema['template_page']]) ) {
			if ( isset($schema['combine_templates']) AND is_array($schema['combine_templates']) ) {
				$template_schema = $this->getTemplateSchema();

				//Handle combining multiple template together with a X,Y offset.
				foreach( $schema['combine_templates'] as $combine_template ) {
					Debug::text('Combining Template Pages... Template: '. $combine_template['template_page'] .' Y: '. $combine_template['y'], __FILE__, __LINE__, __METHOD__, 10);
					$pdf->useTemplate( $this->template_index[$combine_template['template_page']],$combine_template['x']+$this->getTemplateOffsets('x'), $combine_template['y']+$this->getTemplateOffsets('y') );

					$this->setPageOffsets( $combine_template['x'], $combine_template['y']);
					$this->current_template_index = $schema['template_page'];
					$this->initPage( $template_schema );
				}
				unset($combine_templates);
				$this->setPageOffsets( 0, 0 ); //Reset page offsets after each template is initialized.
			} else {
				$pdf->useTemplate( $this->template_index[$schema['template_page']],$this->getTemplateOffsets('x'), $this->getTemplateOffsets('y') );
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
				$pdf->setTextColor( $coordinates['text_color'][0],$coordinates['text_color'][1],$coordinates['text_color'][2] );
			} else {
				$pdf->setTextColor( 0, 0, 0 ); //Black text.
			}

			if ( isset($coordinates['fill_color']) AND is_array( $coordinates['fill_color'] ) ) {
				$pdf->setFillColor( $coordinates['fill_color'][0],$coordinates['fill_color'][1],$coordinates['fill_color'][2] );
				$coordinates['fill'] = 1;
			} else {
				$pdf->setFillColor( 255, 255, 255 ); //White
				$coordinates['fill'] = 0;
			}

			$pdf->setXY( $coordinates['x']+$this->getPageOffsets('x'), $coordinates['y']+$this->getPageOffsets('y') );

			if ( $this->getDebug() == TRUE ) {
				$pdf->setDrawColor( 0, 0 , 255 );
				$coordinates['border'] = 1;
			} else {
				if ( !isset($coordinates['border']) ) {
					$coordinates['border'] = 0;
				}
			}

			if ( isset($schema['multicell']) AND $schema['multicell'] == TRUE ) {
				//Debug::text('Drawing MultiCell... Value: '. $value, __FILE__, __LINE__, __METHOD__, 10);
				$pdf->MultiCell( $coordinates['w'],$coordinates['h'], $value , $coordinates['border'], strtoupper($coordinates['halign']), $coordinates['fill'] );
			} else {
				//Debug::text('Drawing Cell... Value: '. $value, __FILE__, __LINE__, __METHOD__, 10);
				$pdf->Cell( $coordinates['w'],$coordinates['h'], $value , $coordinates['border'], 0, strtoupper($coordinates['halign']), $coordinates['fill'] );
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

    function _outputPDF() {
		//Initialize PDF with template.
		$pdf = $this->getPDFObject();

		//Get location map, start looping over each variable and drawing
		$records = $this->getRecords();

		if ( is_array($records) AND count($records) > 0 ) {

			$template_schema = $this->getTemplateSchema();

			$e=0;
			foreach( $records as $employee_data ) {
				//Debug::Arr($employee_data, 'Employee Data: ', __FILE__, __LINE__, __METHOD__,10);

				$this->arrayToObject( $employee_data ); //Convert record array to object

				$template_page = NULL;

                foreach( $template_schema as $field => $schema ) {

					$this->Draw( $this->$field, $schema );
				}

                $this->resetTemplatePage();

				$e++;
			}
		}

		$this->clearRecords();

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