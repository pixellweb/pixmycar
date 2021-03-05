<?php

namespace Citadelle\PixMyCar\app;


class Document extends Element
{

    const TABLE_ID = 2;

    protected function setMot1Attribute(string $value)
    {
        if (in_array($value, ['02_02_Tableau de bord; ', '03_15_Coffre; ', '04_04_Jante; '])) {
            $this->attributes['is_image'] = true;
        } elseif (!isset($this->attributes['is_image'])) {
            $this->attributes['is_image'] = false;
        }
    }

    protected function setCritere6Attribute(string $value)
    {
        if (in_array($value, ['1AvC34', '2ArP34'])) {
            $this->attributes['is_image'] = true;
        } elseif (!isset($this->attributes['is_image'])) {
            $this->attributes['is_image'] = false;
        }

        $this->attributes['is_illustration'] = $value == '1AvC34';
    }

    protected function setReferenceAttribute(string $value)
    {
        $this->attributes['url'] = "https://citadelle-ws.pixmycar.com/4DCGI/WS/GetPrincipal/" . $this->token . "/" . $value . '/';
        $this->attributes['preview'] = $value;
    }

    protected function setIdDocumentAttribute(string $value)
    {
        $this->attributes['id'] = $value;
    }

}
