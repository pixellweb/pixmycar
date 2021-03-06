<?php

namespace Citadelle\PixMyCar\app;


class Document extends Element
{

    const TABLE_ID = 2;

    protected function getIs_imageAttribute(string $value)
    {
        if (in_array($this->attributes['Mot1'], ['02_02_Tableau de bord; ', '03_15_Coffre; ', '04_04_Jante; '])
            and in_array($this->attributes['Critere6'], ['1AvC34', '2ArP34'])
        ) {
            return true;
        }

        return false;
    }


    protected function getUrlAttribute(string $value)
    {
        $this->attributes['url'] = "https://citadelle-ws.pixmycar.com/4DCGI/WS/GetPrincipal/" . $this->token . "/" . $this->attributes['Reference'] . '/';
        $this->attributes['preview'] = $value;
    }


    protected function getPreviewAttribute(string $value)
    {
        return $this->attributes['Reference'];
    }

    protected function setIdDocumentAttribute(string $value)
    {
        $this->attributes['id'] = $value;
    }

    protected function setCritere6Attribute(string $value)
    {
        $this->attributes['is_illustration'] = $value == '1AvC34';
    }

}
