<?php
 /**
 * @package    AlfrescoClient
 * @author Dominik Danninger 
 *
 * ifresco Client v0.1alpha
 * 
 * Copyright (c) 2011 Dominik Danninger, MAY Computer GmbH
 * 
 * This file is part of "ifresco Client".
 * 
 * "ifresco Client" is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 * 
 * "ifresco Client" is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 * 
 * You should have received a copy of the GNU General Public License
 * along with "ifresco Client".  If not, see <http://www.gnu.org/licenses/>. (http://www.gnu.org/licenses/gpl.html)
 */
class sfWidgetFormSchemaFormatterAlfrescoMetaAdmin extends sfWidgetFormSchemaFormatter
{
  protected
    $rowFormat       = "<li><div class=\"form_row%row_class%\">
                        <div class=\"inRow\">
                        %label% \n %error% <span class=\"rowField\">%field%</span>
                        %help% %hidden_fields%</div>
                        %actions%</div></li>\n",

    $errorRowFormat  = "<div>%errors%</div>",
    $helpFormat      = '<div class="form_help">%help%</div>',
    $decoratorFormat = "<div>\n  %content%</div>";
    
    
  public function formatRow($label, $field, $errors = array(), $help = '', $hiddenFields = null) {
    $row = parent::formatRow(
      $label,
      $field,
      $errors,
      $help,
      $hiddenFields
    );
    
 
    return strtr($row, array(
      '%row_class%' => (count($errors) > 0) ? ' form_row_error' : '',
      '%actions%' => '<div class="metaActions">
      <img src="/images/icons/arrow_out.png" class="moveAction">
      </div>',
    ));
  }

}

?>