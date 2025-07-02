<?php
/**
 * Consulta a Padrón Alcance 10 (ws_sr_padron_a10)
 *
 * @link http://www.afip.gob.ar/ws/ws_sr_padron_a10/manual_ws_sr_padron_a10_v1.1.pdf WS Especificación
 *
 **/

namespace AfipWS\WebService;

use AfipWS\WebService\AfipWebService;

class PadronAlcanceTrece extends AfipWebService
{
  public function __construct($afip)
  {
    parent::__construct($afip);
    $this->setConfig();
  }

  public function setConfig()
  {
    $this->setSoapVersion(SOAP_1_1);
    $this->setWSDL('ws_sr_padron_a13-production.wsdl');
    $this->setUrl('https://aws.afip.gov.ar/sr-padron/webservices/personaServiceA13');
    $this->setWSDLTest('ws_sr_padron_a13.wsdl');
    $this->setUrlTest('https://awshomo.afip.gov.ar/sr-padron/webservices/personaServiceA13');
  }

    /**
    *  Verifica el estado y la disponibilidad de los elementos principales del servicio
    *  (aplicación, autenticación y base de datos).
    *  {@see WS Especificación item 3.1}
    *
    * @return object { appserver => Web Service status,
    * dbserver => Database status, authserver => Autentication
    * server status}
    **/
    public function getEstadoServicio()
    {
      return $this->ejecutar('dummy');
    }

    /**
     * Obtiene del servicio web los detalles del contribuyente {@see WS
     * Especificación item 3.2}
     *
     * @throws Excepción si existe un error en respuesta
     *
     * @return object|null si el contribuyente no existe, return null,
     * si existe, returns persona propiedad de la respuesta {@see
     * WS Especificación item 3.2.2}
    **/
    public function getContribuyenteDetalle($id)
    {
      $afip = $this->getAfip();
      $ta = $afip->getServiceTA('ws_sr_padron_a13');

      $params = array(
        'token'             => $ta->getToken(),
        'sign'              => $ta->getSign(),
        'cuitRepresentada'  => $afip->getCuit(),
        'idPersona'         => $id
      );

      try {
        return $this->ejecutar('getPersona', $params)->persona;
      } catch (\Exception $e) {
        if (strpos($e->getMessage(), 'No existe') !== false)
        {
          return null;
        }
        throw $e;
      }
    }

    public function getPersonByDocumento($id)
    {
      $afip = $this->getAfip();
      $ta = $afip->getServiceTA('ws_sr_padron_a13');

      $params = array(
        'token'             => $ta->getToken(),
        'sign'              => $ta->getSign(),
        'cuitRepresentada'  => $afip->getCuit(),
        'documento'         => $id
      );

      try {
        return $this->ejecutar('getIdPersonaListByDocumento', $params);
      } catch (\Exception $e) {
        if (strpos($e->getMessage(), 'No existe') !== FALSE)
        {
          return NULL;
        }
        throw $e;
      }
    }

    /**
     * Envia un request al servidor de AFIP
     *
     * @param string    $operation  Operación SOAP para hacer
     * @param array     $params     Parámetros para enviar
     *
     * @return mixed Resultados de la operación
     **/
     public function ejecutar($operation, $params = array())
    {
      $results = parent::ejecutar($operation, $params);

      $property = ($operation == 'getPersona')
        ? 'personaReturn'
        : (($operation == 'getIdPersonaListByDocumento')
            ? 'idPersonaListReturn'
            : 'return');

      return $results->{$property};
    }
  }
