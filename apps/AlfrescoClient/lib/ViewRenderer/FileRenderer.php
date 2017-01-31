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
class FileRenderer implements ViewRenderer {
    private $Description = "Inline File Content";
    public function getDescription() {
        return $this->Description;    
    }
    
    private $MimeTypes = array("application/x-javascript",
                               "application/x-httpd-php",
                               "text/x-c",
                               "application/java",
                               "text/x-script.perl",
                               "text/x-script.phyton",
                               "application/php",
                               "text/richtext",
                               "text/javascript",
                               "text/plain",
                               "application/xml",
                               "text/xml",
                               "text/css",
                               "text/plain",
                               );        
    
    public function getMimetypes() {
        return $this->MimeTypes;
    }
    
    public function render($Node,$userObj) {  
        $nodeId = $Node->getId();
        $height = $_GET["height"];
        $ContentData = $Node->cm_content;
        if ($ContentData != null) {
            return $this->renderView($height,$ContentData->getContent());
        }
        return "";
    }
    
    private function renderView($height,$content) {        
        $heightStyle = (!empty($height) ? 'max-height:'.$height.';' : 'max-height:300px'); 

                    
        $html = '
       <link rel="stylesheet" title="Visual Studio" href="/js/highlight/styles/vs.css">    
         <script type="text/javascript" src="/js/highlight/highlight.pack.js"></script>
         <script type="text/javascript">
        // $(document).ready(function() {

          hljs.tabReplace = \'    \';
          hljs.initHighlightingOnLoad();

         // });

          </script>            

        <div style="overflow:auto;width:100%;'.$heightStyle.'"><pre><code>'.$content.'</code></pre></div>';    
        
        
        
        /* $html = '<div style="overflow:auto;width:100%;'.$heightStyle.'"><pre class="brush: js;">'.$content.'</pre></div>


        <link type="text/css" rel="stylesheet" href="/js/syntaxhighlighter/styles/shCore.css"/>      
        <link type="text/css" rel="stylesheet" href="/js/syntaxhighlighter/styles/shCoreEclipse.css"/>      
        <link type="text/css" rel="stylesheet" href="/js/syntaxhighlighter/styles/shThemeEclipse.css"/>      

        <script type="text/javascript" src="/js/syntaxhighlighter/scripts/shCore.js"></script>
        <script type="text/javascript" src="/js/syntaxhighlighter/scripts/shAutoloader.js"></script>
        <script type="text/javascript" src="/js/syntaxhighlighter/scripts/shBrushJScript.js"></script>
        <script type="text/javascript" src="/js/syntaxhighlighter/scripts/shBrushJava.js"></script>
        <script type="text/javascript" src="/js/syntaxhighlighter/scripts/shBrushCpp.js"></script>
        <script type="text/javascript" src="/js/syntaxhighlighter/scripts/shBrushPlain.js"></script>
        <script type="text/javascript" src="/js/syntaxhighlighter/scripts/shBrushPhp.js"></script>
        <script type="text/javascript" src="/js/syntaxhighlighter/scripts/shBrushCss.js"></script>
        <script type="text/javascript" src="/js/syntaxhighlighter/scripts/shBrushXml.js"></script>
        <script type="text/javascript" src="/js/syntaxhighlighter/scripts/shBrushPerl.js"></script>
        <script type="text/javascript" src="/js/syntaxhighlighter/scripts/shBrushPython.js"></script>
        <script type="text/javascript" src="/js/syntaxhighlighter/scripts/shBrushSql.js"></script>
        <script type="text/javascript" src="/js/syntaxhighlighter/scripts/shBrushCSharp.js"></script>

        <script type="text/javascript">
        SyntaxHighlighter.all();
        </script>'; */
        return $html;      
    }
}    
?>
