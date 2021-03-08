<?php

namespace Citadelle\PixMyCar\app;


class Document extends Element
{

    const TABLE_ID = 2;

    protected function getIs_imageAttribute()
    {

        if (in_array($this->attributes['Mot1'], ['02_02_Tableau de bord; ', '03_15_Coffre; ', '04_04_Jante; '])
            or in_array($this->attributes['Critere6'], ['1AvC34', '2ArP34'])
        ) {
            return true;
        }

        return false;
    }


    protected function getUrlAttribute()
    {
        return $this->attributes['url'] = "https://citadelle-ws.pixmycar.com/4DCGI/WS/GetPrincipal/" . $this->token . "/" . $this->attributes['Reference'] . '/';
    }


    protected function getPreviewAttribute()
    {
        return $this->attributes['Reference'];
    }


    protected function getFichierAttribute()
    {
        return $this->attributes['IdDocument'].'.jpg';
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
