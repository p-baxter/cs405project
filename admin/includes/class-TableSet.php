<?php
/* 
 * File: class-TableSet.php
 * 
 * A class for printing out html tables from query results or other data.
 * 
 * The MIT License
 *
 * Copyright 2015 matt.
 *
 * Permission is hereby granted, free of charge, to any person obtaining a copy
 * of this software and associated documentation files (the "Software"), to deal
 * in the Software without restriction, including without limitation the rights
 * to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
 * copies of the Software, and to permit persons to whom the Software is
 * furnished to do so, subject to the following conditions:
 *
 * The above copyright notice and this permission notice shall be included in
 * all copies or substantial portions of the Software.
 *
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 * IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 * FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
 * AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 * LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
 * OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN
 * THE SOFTWARE.
 */


// Charset to output with the CSV header.
const CSV_CHARSET = 'utf-8';

// CSV delimiter character to use in CSV output. 
const CSV_DELIMITER = ',';

// CSV enclosing character to use in CSV output.
const CSV_ENCLOSURE = '"';

class TableSet
{
    static protected $css_table;
    static protected $css_caption = null;
    static protected $css_th;
    static protected $css_td;
    static protected $css_td_odd;
    static protected $css_td_int;
    static protected $css_td_str;
    static protected $css_td_real;
    static protected $css_footer;
    
    /**
     * Number of rows in a good result or 0.
     *
     * @var int
     */
    protected $num_rows;
    
    /**
     * Number of columns in a good result or 0.
     *
     * @var int
     */
    protected $num_cols;
    
    /**
     * Array to hold column names. Array index corresponds to column numbers.
     * With a successful query, each column will have a name value in this array.
     *
     * @var array
     */
    protected $column_names;
    
    /**
     * Array to hold column types. Array index corresponds to column numbers.
     * With a successful query, each column will have a name value in this array.
     *
     * @var array
     */
    protected $column_types;
    
    // Specify width value for the td tag. Not every index is set.
    protected $column_widths;
    
    // Printf format for column output.
    protected $column_formats;
    
    /**
     * A two dimensional array of data. The outside array has rows, and the 
     * inside array has columns.
     *
     * @var array 
     */
    protected $data;
    protected $data_orig;
    
    /**
     * Array to hold the order that columns should be output.
     * For example, with order array( 0 => 0, 1 => 2, 2 => 1)
     * The first column output is column 0, the 2nd is column 2,
     * and the third is column 1.
     *
     * @var array 
     */
//    protected $output_order;
    
    /**
     * Array to hold the order that columns should be output.
     * For example, with array( 0 => 2, 1 => null, 2 => 1)
     * The first column output should be column 0, the 2nd should be column 2,
     * and the third should be column 1.
     *
     * @var array
     */
//    protected $next_column;
    
    // String to print inside the table caption tag.
    // The default value of null prevents the caption tag from being output.
    public $caption;
    
    // String to print inside the tfoot.
    // The default value of null prevents table footer from being output.
    public $footer;
    
    /**
     * Add an additional class to the table tag; Then you can override the
     * default colors when you use multiple TableSets on a page.
     * @var string 
     */
    public $css_extra_class = null;
    
    /**
     * Flag. When true, a column with row numbers is output along with the
     * query results.
     *
     * @var boolean
     */
    protected $show_row_numbers;
    
    
    public $csv_filename;
    
    // String names of the different cell data types this class recognizes.
    // Used for css formatting.
    const TYPE_STRING = 'string';
    const TYPE_INT    = 'int';
    const TYPE_REAL   = 'real';
    
    
    const COLUMN_DEFAULT_TYPE = TableSet::TYPE_STRING;
    
    const CSS_TABLE_CLASS = 'tableset';
    
    /**
     * Initialize column and row counts to 0. Data is empty.
     */
    public function __construct()
    {
        $this->num_cols = 0;
        $this->num_rows = 0;
        $this->caption = null;
        $this->footer = null;
        $this->column_names = array();
        $this->column_types = array();
        $this->column_widths = array();
        $this->column_widths = array();
        $this->column_formats = array();
        $this->show_row_numbers = true;
        
        $this->data = array();
        $this->data_orig = array();
//        $this->next_column = array();
//        $this->output_order = array();
        
        // Default filename to send to browser upon download.
        $this->csv_filename = 'results.csv';
    }
    // end __construct().
    
    /**
     * Setup an array that holds CSS data.
     * 
     */
    static public function set_default_css()
    {
        TableSet::$css_table = array();
        TableSet::$css_caption = array();
        TableSet::$css_td = array();
        TableSet::$css_th = array();
        TableSet::$css_footer = array();
                
        TableSet::$css_table['font-family'] = 'courier new, courier,monospace';
        TableSet::$css_table['font-size'] = '12pt';
        TableSet::$css_table['border-spacing'] = '0px';
        TableSet::$css_table['border-left'] = 'solid 1px #777';
        TableSet::$css_table['border-bottom'] = 'solid 1px #777';
        TableSet::$css_table['background-color'] = '#999';
        
        TableSet::$css_th['font-family'] = 'arial';
        TableSet::$css_th['background-color'] = 'firebrick';
        
        TableSet::$css_th['padding'] = '0px 10px;';
        TableSet::$css_th['border-style'] = 'solid';
        TableSet::$css_th['border-width'] = '1px 1px 0px 0px';
        TableSet::$css_th['border-color'] = '#777';
        
        TableSet::$css_caption['font-family'] = 'arial';
        TableSet::$css_caption['background-color'] = '#333';
        
        TableSet::$css_td['padding'] = '0px 10px;';
        TableSet::$css_td['border-style'] = 'solid';
        TableSet::$css_td['border-width'] = '1px 1px 0px 0px';
        TableSet::$css_td['border-color'] = '#777';
        
        TableSet::$css_td_int = array('text-align' => 'right');
        TableSet::$css_td_real = array('text-align' => 'right');
        TableSet::$css_td_str = null;
        
        TableSet::$css_td_odd = array('background-color' => '#ccc');
        
        TableSet::$css_footer['text-align'] = 'right';
        
//        $this->css[self::CSS_SEL_CLASSTABLESET.' p.rightDim'] = 'color:#aaa; margin:6px 10px 30px; text-align: right; width:69%;';
    } 
    // end set_default_css().
    
    
    /**
     * Set a CSS value for the main table.tableset.
     * 
     * @param string $key
     * @param string $val
     */
    static public function set_css_table_value( $key, $val)
    {
        $this->css_table[$key] = $val;
    }
    // end set_css_table_value().
    
    static public function set_css_td_value( $key, $val)
    {
        $this->css_td[$key] = $val;
    }
    
    static public function set_css_th_value( $key, $val)
    {
        $this->css_th[$key] = $val;
    }
    
    static public function set_css_tdint_value( $key, $val)
    {
        $this->css_td_int[$key] = $val;
    }
    
    static public function set_css_tdreal_value( $key, $val)
    {
        $this->css_td_real[$key] = $val;
    }
    
    static public function set_css_tdstr_value( $key, $val)
    {
        $this->css_td_str[$key] = $val;
    }
    
    static public function set_css_caption_value( $key, $val)
    {
        TableSet::$css_caption[$key] = $val;
    }
    
    static public function set_css_footer_value($key, $val)
    {
        TableSet::$css_footer[$key] = $val;
    }
    
    static public function set_css_tdOdd_value($key, $val)
    {
        TableSet::$css_td_odd[$key] = $val;
    }
    
    /**
     * Print the CSS defined in $this->css_table and other css fields.
     * This output should be surrounded with the style tag.
     * 
     */
    static public function print_css()
    {
        echo "/* Start TableSet css. */\n";
        
        // Print the main table style.
        if( count(TableSet::$css_table) > 0 )
        {
            echo 'table.'.self::CSS_TABLE_CLASS . '{';
            foreach(TableSet::$css_table as $key => $val )
            {
                echo $key . ':' . $val . ';';
            }
            echo "}\n";
        }
        
        // Print the style for TH.
        if( count(TableSet::$css_th) > 0 )
        {
            echo 'table.'.self::CSS_TABLE_CLASS . ' th {';
            foreach(TableSet::$css_th as $key => $val )
            {
                echo $key . ':' . $val . ';';
            }
            echo "}\n";
        }
        
        // Print the style for TD.
        if( count(TableSet::$css_td) > 0 )
        {
            echo 'table.'.self::CSS_TABLE_CLASS . ' td {';
            foreach(TableSet::$css_td as $key => $val )
            {
                echo $key . ':' . $val . ';';
            }
            echo "}\n";
        }
        
        
        // Print the style for caption.
        if( count(TableSet::$css_caption) > 0 )
        {
            echo 'table.'.self::CSS_TABLE_CLASS . ' caption {';
            foreach(TableSet::$css_caption as $key => $val )
            {
                echo $key . ':' . $val . ';';
            }
            echo "}\n";
        }
        
        
        // Print the style for td.int .
        if( count(TableSet::$css_td_int) > 0 )
        {
            echo 'table.'.self::CSS_TABLE_CLASS . ' td.'.self::TYPE_INT.' {';
            foreach(TableSet::$css_td_int as $key => $val )
            {
                echo $key . ':' . $val . ';';
            }
            echo "}\n";   
        }
        
        // Print the style for td.real .
        if( count(TableSet::$css_td_real) > 0 )
        {
            echo 'table.'.self::CSS_TABLE_CLASS . ' td.'.self::TYPE_REAL.' {';
            foreach(TableSet::$css_td_real as $key => $val )
            {
                echo $key . ':' . $val . ';';
            }
            echo "}\n";
        }
        
        // Print the style for td.str .
        if( count(TableSet::$css_td_str) > 0 )
        {
            echo 'table.'.self::CSS_TABLE_CLASS . ' td.'.self::TYPE_STRING.' {';
            foreach(TableSet::$css_td_str as $key => $val )
            {
                echo $key . ':' . $val . ';';
            }
            echo "}\n";
        }
        
        // Print the footer style.
        if( count(TableSet::$css_footer) > 0 )
        {
            echo 'table.'.self::CSS_TABLE_CLASS . ' tfoot td {';
            foreach(TableSet::$css_footer as $key => $val )
            {
                echo $key . ':' . $val . ';';
            }
            echo "}\n";
        }
        
        // Print style for odd rows.
        if( count(TableSet::$css_td_odd) > 0 )
        {
            echo 'table.'.self::CSS_TABLE_CLASS . ' tr.odd {';
            foreach(TableSet::$css_td_odd as $key => $val )
            {
                echo $key . ':' . $val . ';';
            }
            echo "}\n";
        }
        
        echo 'table.'.self::CSS_TABLE_CLASS.' tbody tr:hover {background-color:white;}'."\n";
        
        echo "/* end TableSet css. */\n";
    }
    // end print_css().

    
    /**
     * Set the internal data table with $data.
     * Data must be a 2D array with integer indexes.
     * Returns false if $data was of the wrong format.
     * Returns true if $data was accepted and $this->data was set.
     * 
     * @param array $data
     * @return boolean
     */
    public function set_data($data )
    {
        $retval = false;
        if( is_array($data))
        {
            $rowcnt = count($data);
            
            if($rowcnt > 0 && isset($data[0]) && is_array($data[0]))
            {
                $this->num_cols = count($data[0]);
                
                $this->num_rows = $rowcnt;
                
                $this->data = $data;

//                // Set the column ordering at default.
                $col=0;
                for(; $col < $this->num_cols; $col++ )
                {
//                    $this->next_column[$col] = $col + 1;
//                    $this->output_order[$col] = $col;
                    
                    // Default to string type.
                    $this->column_types[$col] = self::TYPE_STRING;
                }
////                // The last column has no next column.
////                $this->next_column[$col - 1] = null;
                
                $this->footer = $this->num_rows . ' rows';
                
                $retval = true;
            }
            // end if inside array exists.
        }
        // end if data is array.
        
        return $retval;
    }
    // end set_data().
    
    
    
    /**
     * Print HTTP headers that make the browser prompt the user to download
     * the subsequent data as a CSV file.
     * 
     */
    public function print_csv_headers()
    {
        // Tell the browser to expect a csv file
        // Note: try application/octet-stream if the browser doesn't try to save the file.
        // It works in Firefox 36 on Mac. MD.
        header('Content-Type: text/csv; charset='.CSV_CHARSET, TRUE);
    
        // Suggest a filename for the browser to use when prompting the user to
        // save.
        header('Content-Disposition: attachment; filename="'.$this->csv_filename.'"');
    }
    // end print_csv_headers().
    
    /**
     * Fetch the query results and print the output as CSV data.
     * 
     * Reference: 
     * http://code.stephenmorley.org/php/creating-downloadable-csv-files/
     * 
     * Pre-Conditions: print_table_html() must not have been called before this
     *    function. Otherwise, the query isn't re-fetched.
     * 
     *    To use column names that are not the raw SQL fields, call 
     *    $this->set_column_name() on the desired columns.
     * 
     * Post-Condition: Rows from the SQL query have been fetched, and the
     *    results were output to the browser as CSV data.
     */
    public function print_table_csv()
    {
        // Create a file pointer connected to the output stream.
        $output = fopen('php://output', 'w');

        // Print the column names.
        fputcsv($output, $this->column_names, CSV_DELIMITER, CSV_ENCLOSURE);

        // Iterate over each data row.
        for($row=0; $row < $this->num_rows; $row++)
        {
//            // Create the output row.
//            $rowout = array();
//            for($col=0; $col < $this->num_cols; $col++)
//            {
//                $outc = $this->output_order[$col];
//                
//                $val = $this->data[$row][$outc];
//
//                $rowout[] = $val;
//            }
//            // done creating the output row.
         
//            fputcsv($output, $rowout, CSV_DELIMITER, CSV_ENCLOSURE);
            fputcsv($output, $this->data[$row], CSV_DELIMITER, CSV_ENCLOSURE);
        }
        // done iterating over each row.

        // necessary? md.
        fclose($output);
    }
    // end print_table_csv().
    
    /**
     * Fetch the results of the database query and print HTML table out to the
     * browser. If a caption is specified, then the table uses that caption.
     * Likewise, a specified footer is output.
     * 
     * Pre-Conditions: print_table_csv() must not have been called before this
     *    function. Otherwise, the query isn't re-fetched.
     */
    public function print_table_html()
    {
        echo '<table class="'.self::CSS_TABLE_CLASS;
        
        if( $this->css_extra_class != null )
            echo ' '.$this->css_extra_class;
        
        echo '">'."\n"
        . ($this->caption ? ' <caption>'.$this->caption.'</caption>' . "\n" : '')
        . " <thead><tr>\n";

        // Print the column heading for row numbers.
        if( $this->show_row_numbers)
        {
            echo "  <th>&nbsp;</th>\n";
        }

        // Print column headers for each column.
        for($col=0; $col < $this->num_cols; $col++)
        {
            echo '  <th';
            if( isset($this->column_widths[$col]))
            {
                echo ' width="'.$this->column_widths[$col].'"';
            }
            echo '>'.$this->column_names[$col]."</th>\n";
        }
        // done printing column headers.

        echo " </tr></thead>\n";

        // Print the table footer.
        if( $this->footer )
        {
            $span = $this->num_cols;
            if( $this->show_row_numbers ) $span += 1;
            echo ' <tfoot><tr><td colspan="'.$span.'">'.$this->footer.'</td></tr></tfoot>'."\n";
        }

        echo " <tbody>\n";

        // Fetch each result row and print it.
        $rowCnt = 1;
        for($row=0; $row < $this->num_rows; $row++)
        {
            $css_class = '';
            if( ($row % 2) == 1 )
            {
                $css_class .= ' class="odd"';
            }
            
            echo "  <tr$css_class>\n";

            // Print the row number and increment the counter.
            if( $this->show_row_numbers)
            {
                echo '   <td class="rowNo">'. $rowCnt++ . "</td>\n";
            }
            
            // Print each column value in this row.
            for($col=0; $col < $this->num_cols; $col++)
            {
                
                
                // Get the output column number. If column ordering didn't change,
                // then this is the same value as $col.
//                $outc = $this->output_order[$col];
                
//                echo '   <td class="'. $this->column_types[$outc] . '">'.$this->data[$row][$outc] . "</td>\n";
                
                $outstr = $this->data[$row][$col];
                if( isset($this->column_formats[$col]))
                {
                    $outstr = sprintf($this->column_formats[$col], $this->data[$row][$col]);
                }
                
                echo '   <td class="'. $this->column_types[$col] . '">'.$outstr . "</td>\n";
            }
            // done printing each column in this row.

            echo "  </tr>\n";
        }
        // done fetching each result row.

        echo "</tbody></table>\n";
    }
    // end print_table_string().
    
    /**
     * Sets a column name. Returns false if the column number was out of bounds.
     * 
     * @param int $colNo
     * @param string $name
     * @return boolean
     */
    public function set_column_name($colNo, $name)
    {
        if( ! $this->column_exists($colNo))
            return false;
        
        $this->column_names[$colNo] = $name;
        
        return true;
    }
    // end set_column_name().
    
    /**
     * Replace all column names with values in $arr.
     * 
     * @param array $arr
     * @return boolean
     */
    public function set_column_names($arr)
    {
        if( !is_array($arr))
            return false;
        
        if( count($arr) != $this->num_cols)
            return false;
        
        $this->column_names = $arr;
        
        return true;
    }
    // end set_column_names().
    
    /**
     * Sets a column type. Returns false if the column number was out of bounds.
     * Type should be 
     * 
     * @param int $colNo
     * @param string $type
     * @return boolean
     */
    public function set_column_type($colNo, $type)
    {
        if( ! $this->column_exists($colNo))
            return false;
        
        $this->column_types[$colNo] = $type;
        
        return true;
    }
    // end set_column_name().
    
    /**
     * Set all column types to the values in $types.
     * Types should be TableSet::TYPE_INT, TYPE_REAL, or TYPE_STRING.
     * 
     * @param array $types
     * @return boolean
     */
    public function set_column_types( $types )
    {
        if( !is_array($types) || count($types) != $this->num_cols)
            return false;
        
        $this->column_types = $types;
        return true;
    }
    
    public function set_column_format($colNo, $format)
    {
        if( ! $this->column_exists($colNo))
            return false;
        
        $this->column_formats[$colNo] = $format;
        
        return true;
    }
    
    public function set_column_formats($formats)
    {
        if( !is_array($formats) || count($formats) != $this->num_cols)
            return false;
        
        $this->column_formats = $formats;
        return true;
    }
    
    /**
     * Sets a column width. Returns false if the column number was out of bounds.
     * Width goes into the TH tag and should be of the form "10%" or "200px".
     * 
     * @param int $colNo
     * @param string $val
     * @return boolean
     */
    public function set_column_width($colNo, $val)
    {
        if( ! $this->column_exists($colNo))
            return false;
        
        $this->column_widths[$colNo] = $val;
        
        return true;
    }
    // end set_column_name().
    
    /**
     * Replace cell data for the specified column. The $arr argument should be
     * an associative array. For each cell value, if the value matches a key
     * in $arr, then the corresponding value in $arr[matching_key] replaces
     * the cell data.
     * 
     * A backup of the column is made before anything is replaced.
     * (That allows you to display descriptive text but still use numeric
     * data in href links, for instance.)
     * 
     * @param int $colNo
     * @param array $arr
     * @return boolean
     */
    public function replace_column_values($colNo, $arr )
    {
        if( ! $this->column_exists($colNo))
            return false;
       
        if(! is_array($arr))
            return false;
        
        // If we haven't already, backup the column data.
        if( ! isset($this->data_orig[$colNo]))
            $this->data_orig[$colNo] = $this->data[$colNo];
        
        // Replace column values in each row.
        for($row=0; $row < $this->num_rows; $row++)
        {
            $val = $this->data[$row][$colNo];
            // See if there exists a mapping to swap out the cell
            // value with a more descriptive value.
            if(  isset($arr[$val]) )
            {
                $this->data[$row][$colNo] = $arr[$val];
            }
        }
        // done iterating over each row.
        
    }
    // end replace_column_values().
    
    
    /**
     * Returns false if the column number was out of bounds; true otherwise.
     * 
     * @param int $colNo
     * @return boolean
     */
    protected function column_exists($colNo)
    {
        if( $colNo < 0 || $colNo >= $this->num_cols)
            return false;
        return true;
    }
    // end column_exists().
    
    /**
     * Returns false if the row number was out of bounds; true otherwise.
     * 
     * @param int $rowNo
     * @return boolean
     */
    protected function row_exists($rowNo)
    {
        if( $rowNo < 0 || $rowNo >= $this->num_rows)
            return false;
        return true;
    }
    
    /**
     * Set the flag to show or hide the column containing row numbers.
     * 
     * @param boolean $show
     */
    public function show_row_numbers($show)
    {
        if( $show )
        {
            $this->show_row_numbers = true;
        }
        else
        {
            $this->show_row_numbers = false;
        }
    }
    // end showhide_row_numbers().
    
    /**
     * Return the number of rows in the table data.
     * 
     * @return int
     */
    public function get_num_rows()
    {
        return $this->num_rows;
    }
    
    /**
     * Return the number of columns in the table data.
     * 
     * @return int
     */
    public function get_num_cols()
    {
        return $this->num_cols;
    }
    // end get_num_cols().
    
    /**
     * Returns the column name (header) given the column number.
     * 
     * 
     * @param int $colNo
     * @return mixed
     * Returns false if column doesn't exist, a string containing the name if
     * it does exist.
     */
    public function get_col_name($colNo)
    {
        if( ! $this->column_exists($colNo))
            return false;
        
        return $this->column_names[$colNo];
    }
    // end get_col_name().
    
    /**
     * Add a column to the end of the column set.
     * 
     * @param string $name
     */
    public function add_column( $name )
    {
        $newColNo = $this->num_cols;
        $this->num_cols += 1;
        
        // Add empty data to the new column.
        for($row=0; $row < $this->num_rows; $row++)
        {
            $this->data[$row][$newColNo] = null;
        }
        
        $this->column_names[$newColNo] = $name;
    }
    // done add_column().
    
    /**
     * Return the value at the specified cell, or null if the cell doesn't
     * exist.
     * If $orig is true, returns the original data that wasn't changed with
     * a call to replace_column_values().
     * 
     * @param int $rowNo
     * @param int $colNo
     * @param boolean $orig
     * @return mixed
     */
    public function get_value_at($rowNo, $colNo, $orig = false)
    {
        if( !$this->column_exists($colNo) || ! $this->row_exists($rowNo))
            return null;
        
        // See if there is backed-up data at this cell.
        if( $orig && isset($this->data_orig[$rowNo][$colNo]))
            return $this->data_orig[$rowNo][$colNo];
        
        return $this->data[$rowNo][$colNo];
    }
    // end get_value_at().
    
    /**
     * Set the cell value at row, col.
     * 
     * @param int $rowNo
     * @param int $colNo
     * @param mixed $value
     * @return boolean
     */
    public function set_value_at($rowNo, $colNo, $value )
    {
        if( !$this->column_exists($colNo) || ! $this->row_exists($rowNo))
            return false;
        
        // Backup the original value.
        $this->data_orig[$rowNo][$colNo] = $this->data[$rowNo][$colNo];
        
        $this->data[$rowNo][$colNo] = $value;
    }
    // end set_value_at().


    public function replace_headers_by_map($map, $startCol, $colPrefix)
    {
        if( !is_array($map))
        {
            return false;
        }

        if( ! $this->column_exists($startCol))
            return false;
        // done parameter checking.
//        
//        $prefLen = strlen($colPrefix);

        // Replace column headers with descriptive ones.
        if( $this->get_num_cols() > $startCol )
        {
            // For each column after closing price.
            for($colNo=$startCol; $colNo < $this->num_cols; $colNo++ )
            {
                $cname = str_replace($colPrefix, '', $this->column_names[$colNo]);
                
                // the volType column name should be "vol" followed by a number.
                // remove "vol" and swap a description for the number.
//                $cname = substr($this->column_names[$colNo], 3);
                if( isset($map[$cname]))
                {
                    $this->column_names[$colNo] = $map[$cname];
                }
            }
            // done iterating over columns.
        }
        // done replacing column headers.

        return true;
    }
    //
    
}
// end class MysqlResultTable.
