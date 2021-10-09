<? php
/ **
 * Classe PHP para converter coordenadas Latitude + Longitude em UTM e vice-versa.
 * /
classe  GpointConverter
{
	const  K0 = 0,9996 ;
	/ **
	 * Raio Equatorial
	 * @var inteiro
	 * /
	private  $ a ;
	/ **
	 * Quadrado de excentricidade
	 * @var float
	 * /
	private  $ eccSquared ;
	 função  pública __construct ( $ datumName = 'ETRS89' )
	{
		$ this -> setEllipsoid ( $ datumName );
		$ this -> datum = $ datumName ;
	}
	/ **
	 * Converta latitude / longitude em coordenadas UTM. Equações do Boletim 1532 do USGS
	 * Calcula automaticamente a zona, com regras de zona especiais adicionadas para Dinamarca e Svalbard.
	 * @param float $ latitude
	 * @param float $ longitude
	 * /
	public  function  convertLatLngToUtm ( $ latitude , $ longitude )
	{
		// Certifique-se de que a longitude esteja entre -180,00 .. 179,9
		$ LongTemp = ( $ longitude + 180 ) - ( int ) (( $ longitude + 180 ) / 360 ) * 360 - 180 ; // -180,00 .. 179,9;
		$ LatRad = deg2rad ( $ latitude );
		$ LongRad = deg2rad ( $ LongTemp );
		if ( $ LongTemp > = 8 && $ LongTemp <= 13 && $ latitude > 54,5 && $ latitude < 58 ) { // Zonas especiais para Dinamarca: http://www.kms.dk/Referencenet/Referencesystemer/UTM_ETRS89/
			$ ZoneNumber = 32 ;
		} else  if ( $ latitude > = 56.0 && $ latitude < 64.0 && $ LongTemp > = 3.0 && $ LongTemp < 12.0 ) { // Do código C ++
			$ ZoneNumber = 32 ;
		} else {
			$ ZoneNumber = ( int ) (( $ LongTemp + 180 ) / 6 ) + 1 ;
			// Zonas especiais para Svalbard
			if ( $ latitude > = 72,0 && $ latitude < 84,0 ) {
				if ( $ LongTemp > = 0,0 && $ LongTemp < 9,0 ) {
					$ ZoneNumber = 31 ;
				} else  if ( $ LongTemp > = 9.0 && $ LongTemp < 21.0 ) {
					$ ZoneNumber = 33 ;
				} else  if ( $ LongTemp > = 21,0 && $ LongTemp < 33,0 ) {
					$ ZoneNumber = 35 ;
				} else  if ( $ LongTemp > = 33,0 && $ LongTemp < 42,0 ) {
					$ ZoneNumber = 37 ;
				}
			}
		}
		$ LongOrigin = ( $ ZoneNumber - 1 ) * 6 - 180 + 3 ;  // + 3 coloca a origem no meio da zona
		$ LongOriginRad = deg2rad ( $ LongOrigin );
		$ UTMZone = $ ZoneNumber . self :: getUtmLetterDesignator ( $ latitude );
		$ eccPrimeSquared = ( $ this -> eccSquared ) / ( 1 - $ this -> eccSquared );
		$ N = $ this -> a / sqrt ( 1 - $ this -> eccSquared * sin ( $ LatRad ) * sin ( $ LatRad ));
		$ T = tan ( $ LatRad ) * tan ( $ LatRad );
		$ C = $ eccPrimeSquared * cos ( $ LatRad ) * cos ( $ LatRad );
		$ A = cos ( $ LatRad ) * ( $ LongRad - $ LongOriginRad );
		$ M = $ this -> a * (( 1 	- $ this -> eccSquared / 4 		- 3 * $ this -> eccSquared * $ this -> eccSquared / 64 	- 5 * $ this -> eccSquared * $ this -> eccSquared * $ this -> eccSquared / 256 ) * $ LatRad 
					- ( 3 * $ this -> eccSquared / 8 	+ 3 * $ this -> eccSquared * $ this -> eccSquared / 32 	+ 45 * $ this -> eccSquared * $ this -> eccSquared * $ this -> eccSquared / 1024 ) * sin ( 2 * $ LatRad )
										+ ( 15 * $ this -> eccSquared * $ this -> eccSquared / 256 + 45 * $ this -> eccSquared * $ this -> eccSquared * $ this -> eccSquared / 1024 ) * sin ( 4 * $ LatRad )
										- ( 35 * $ this -> eccSquared * $ this -> eccSquared * $ this -> eccSquared / 3072 ) * sin ( 6 * $ LatRad ));
		$ UTMEasting = ( float ) ( self :: K0 * $ N * ( $ A + ( 1 - $ T + $ C ) * $ A * $ A * $ A / 6
						+ ( 5 - 18 * $ T + $ T * $ T + 72 * $ C - 58 * $ eccPrimeSquared ) * $ A * $ A * $ A * $ A * $ A / 120 )
						+ 500000,0 );
		$ UTMNorthing = ( float ) ( self :: K0 * ( $ M + $ N * tan ( $ LatRad ) * ( $ A * $ A / 2 + ( 5 - $ T + 9 * $ C + 4 * $ C * $ C ) * $ A * $ A * $ A * $ A /24
					 + ( 61 - 58 * $ T + $ T * $ T + 600 * $ C - 330 * $ eccPrimeSquared ) * $ A * $ A * $ A * $ A * $ A * $ A / 720 )));
		if ( $ latitude < 0 )	 $ UTMNorthing + = 10000000.0 ; 
		$ UTMNorthing = ( int ) rodada ( $ UTMNorthing );
		$ UTMEasting = ( int ) round ( $ UTMEasting );
		 array de retorno ( $ UTMEasting , $ UTMNorthing , $ UTMZone );
	}
	/ **
	 * Converta UTM para Longitude / Latitude
	 * @param integer $ UTMEasting
	 * @param integer $ UTMNorthing
	 * @param string $ UTMZone
	 * /
	public  function  convertUtmToLatLng ( $ UTMEasting , $ UTMNorthing , $ UTMZone )
	{
		$ e1 = ( 1 - sqrt ( 1 - $ this -> eccSquared )) / ( 1 + sqrt ( 1 - $ this -> eccSquared ));
		$ x = $ UTMEasting - 500000,0 ; // remove 500.000 metros de deslocamento para longitude
		$ y = $ UTMNorthing ;
		sscanf ( $ UTMZone , "% d% s" , $ ZoneNumber , $ ZoneLetter );
		if ( strcmp ( 'N' , $ ZoneLetter ) <= 0 ) {
			$ Hemisfério Norte = 1 ; // o ponto está no hemisfério norte
		} else {
			$ Hemisfério Norte = 0 ; // ponto está no hemisfério sul
			$ y - = 10000000,0 ; // remove o deslocamento de 10.000.000 metros usado para o hemisfério sul
		}
		$ LongOrigin = ( $ ZoneNumber - 1 ) * 6 - 180 + 3 ;  // + 3 coloca a origem no meio da zona
		$ eccPrimeSquared = ( $ this -> eccSquared ) / ( 1 - $ this -> eccSquared );
		$ M = $ y / self :: K0 ;
		$ mu = $ M / ( $ this -> a * ( 1 - $ this -> eccSquared / 4 - 3 * $ this -> eccSquared * $ this -> eccSquared / 64 - 5 * $ this -> eccSquared * $ this -> eccSquared * $ this -> eccSquared / 256 ));
		$ phi1Rad = $ mu 	+ ( 3 * $ e1 / 2 - 27 * $ e1 * $ e1 * $ e1 / 32 ) * sin ( 2 * $ mu )
					+ ( 21 * $ e1 * $ e1 / 16 - 55 * $ e1 * $ e1 * $ e1 * $ e1 / 32 ) * sin ( 4 * $ mu )
					+ ( 151 * $ e1 * $ e1 * $ e1 / 96 ) * sin ( 6 * $ mu );
		$ phi1 = rad2deg ( $ phi1Rad );
		$ N1 = $ this -> a / sqrt ( 1 - $ this -> eccSquared * sin ( $ phi1Rad ) * sin ( $ phi1Rad ));
		$ T1 = tan ( $ phi1Rad ) * tan ( $ phi1Rad );
		$ C1 = $ eccPrimeSquared * cos ( $ phi1Rad ) * cos ( $ phi1Rad );
		$ R1 = $ this -> a * ( 1 - $ this -> eccSquared ) / pow ( 1 - $ this -> eccSquared * sin ( $ phi1Rad ) * sin ( $ phi1Rad ), 1,5 );
		$ D = $ x / ( $ N1 * self :: K0 );
		$ Lat = $ phi1Rad - ( $ N1 * tan ( $ phi1Rad ) / $ R1 ) * ( $ D * $ D / 2 - ( 5 + 3 * $ T1 + 10 * $ C1 - 4 * $ C1 * $ C1 - 9 * $ eccPrimeSquared ) * $ D * $ D * $ D* $ D / 24
						+ ( 61 + 90 * $ T1 + 298 * $ C1 + 45 * $ T1 * $ T1 - 252 * $ eccPrimeSquared - 3 * $ C1 * $ C1 ) * $ D * $ D * $ D * $ D * $ D * $ D / 720 );
		$ Lat = rad2deg ( $ Lat );
		$ Long = ( $ D - ( 1 + 2 * $ T1 + $ C1 ) * $ D * $ D * $ D / 6 + ( 5 - 2 * $ C1 + 28 * $ T1 - 3 * $ C1 * $ C1 + 8 * $ eccPrimeSquared + 24 * $ T1 * $ T1)
						* $ D * $ D * $ D * $ D * $ D / 120 ) / cos ( $ phi1Rad );
		$ Long = $ LongOrigin + rad2deg ( $ Long );
		 array de retorno ( $ Lat , $ Long );
	}
	/ **
	 * Elipsóides de referência derivados do site de Peter H. Dana: 
	 * http://www.utexas.edu/depts/grg/gcraft/notes/datum/elist.html
	 * Departamento de Geografia da Universidade do Texas em Austin
	 * Internet: pdana@mail.utexas.edu 22/03/95
	 * Fonte:
	 * Agência de Mapeamento de Defesa. 1987b. Relatório Técnico DMA: Suplemento ao Relatório Técnico de 1984 do Sistema Geodésico Mundial do Departamento de Defesa. Parte I e II.
	 * Washington, DC: Defense Mapping Agency
	 * Nomes alternativos adicionados para fácil compatibilidade por hd@onlinecity.dk
	 * @param string $ name
	 * /
	public  function  setEllipsoid ( $ name )
	{
		switch ( $ name ) {
			case  'Airy' : $ this -> a = 6377563 ; $ this -> eccSquared = 0,00667054 ; pausa ;
			case  'Australian National' : $ this -> a = 6378160 ; $ this -> eccSquared = 0,006694542 ; pausa ;
			case  'Bessel 1841' : $ this -> a = 6377397 ; $ this -> eccSquared = 0,006674372 ; pausa ;
			caso  'Bessel 1841 Nâmbia' : $ this -> a = 6377484 ; $ this -> eccSquared = 0,006674372 ; pausa ;
			case  'Clarke 1866' : $ this -> a = 6378206 ; $ this -> eccSquared = 0,006768658 ; pausa ;
			case  'Clarke 1880' : $ this -> a = 6378249 ; $ this -> eccSquared = 0,006803511 ; pausa ;
			caso  'Everest' : $ this -> a = 6377276 ; $ this -> eccSquared = 0,006637847 ; pausa ;
			estojo  'Fischer 1960 Mercury' : $ this -> a = 6378166 ; $ this -> eccSquared = 0,006693422 ; pausa ;
			case  'Fischer 1968' : $ this -> a = 6378150 ; $ this -> eccSquared = 0,006693422 ; pausa ;
			caso  'GRS 1967' : $ this -> a = 6378160 ; $ this -> eccSquared = 0,006694605 ; pausa ;
			caso  'GRS 1980' : $ this -> a = 6378137 ; $ this -> eccSquared = 0,00669438 ; pausa ;
			case  'Helmert 1906' : $ this -> a = 6378200 ; $ this -> eccSquared = 0,006693422 ; pausa ;
			case  'Hough' : $ this -> a = 6378270 ; $ this -> eccSquared = 0,00672267 ; pausa ;
			case  'Internacional' : $ this -> a = 6378388 ; $ this -> eccSquared = 0,00672267 ; pausa ;
			case  'Krassovsky' : $ this -> a = 6378245 ; $ this -> eccSquared = 0,006693422 ; pausa ;
			case  'Modificado Airy' : $ this -> a = 6377340 ; $ this -> eccSquared = 0,00667054 ; pausa ;
			case  'Everest modificado' : $ this -> a = 6377304 ; $ this -> eccSquared = 0,006637847 ; pausa ;
			case  'Fischer modificado 1960' : $ this -> a = 6378155 ; $ this -> eccSquared = 0,006693422 ; pausa ;
			caso  'América do Sul 1969' : $ this -> a = 6378160 ; $ this -> eccSquared = 0,006694542 ; pausa ;
			case  'WGS 60' : $ this -> a = 6378165 ; $ this -> eccSquared = 0,006693422 ; pausa ;
			case  'WGS 66' : $ this -> a = 6378145 ; $ this -> eccSquared = 0,006694542 ; pausa ;
			caso  'WGS 72' : $ this -> a = 6378135 ; $ this -> eccSquared = 0,006694318 ; pausa ;
			case  'ED50' : $ this -> a = 6378388 ; $ this -> eccSquared = 0,00672267 ; pausa ; // Elipsóide Internacional
			case  'WGS 84' :
			case  'EUREF89' : // O desvio máximo de WGS 84 é de 40 cm / km, consulte http://ocq.dk/euref89 (em dinamarquês)
			case  'ETRS89' : // Mesmo que EUREF89
				$ this -> a = 6378137 ;
				$ this -> eccSquared = 0,00669438 ;
				pausa ;
			padrão :
				throw  new \ InvalidArgumentException ( 'Nenhum dado ecclipsoid associado a datum desconhecido:' . $ name );
		}
	}
	/ **
	 * Obtenha o designador de letra UTM para uma determinada latitude.
	 * retorna 'Z' se a latitude estiver fora dos limites UTM de 84N a 80S
	 * @param float $ latitude
	 * /
	public  static  function  getUtmLetterDesignator ( $ latitude )
	{
		switch ( $ latitude ) {
			case (( 84 > = $ latitude ) && ( $ latitude > = 72 )): return  'X' ;
			case (( 72 > $ latitude ) && ( $ latitude > = 64 )): return  'W' ;
			case (( 64 > $ latitude ) && ( $ latitude > = 56 )): return  'V' ;
			case (( 56 > $ latitude ) && ( $ latitude > = 48 )): return  'U' ;
			case (( 48 > $ latitude ) && ( $ latitude > = 40 )): return  'T' ;
			case (( 40 > $ latitude ) && ( $ latitude > = 32 )): return  'S' ;
			case (( 32 > $ latitude ) && ( $ latitude > = 24 )): return  'R' ;
			case (( 24 > $ latitude ) && ( $ latitude > = 16 )): return  'Q' ;
			case (( 16 > $ latitude ) && ( $ latitude > = 8 )): return  'P' ;
			case (( 8 > $ latitude ) && ( $ latitude > = 0 )): return  'N' ;
			case (( 0 > $ latitude ) && ( $ latitude > = - 8 )): return  'M' ;
			case ((- 8 > $ latitude ) && ( $ latitude > = - 16 )): return  'L' ;
			case ((- 16 > $ latitude ) && ( $ latitude > = - 24 )): return  'K' ;
			case ((- 24 > $ latitude ) && ( $ latitude > = - 32 )): return  'J' ;
			case ((- 32 > $ latitude ) && ( $ latitude > = - 40 )): return  'H' ;
			case ((- 40 > $ latitude ) && ( $ latitude > = - 48 )): return  'G' ;
			case ((- 48 > $ latitude ) && ( $ latitude > = - 56 )): return  'F' ;
			case ((- 56 > $ latitude ) && ( $ latitude > = - 64 )): return  'E' ;
			case ((- 64 > $ latitude ) && ( $ latitude > = - 72 )): return  'D' ;
			case ((- 72 > $ latitude ) && ( $ latitude > = - 80 )): return  'C' ;
			padrão : retorna  'Z' ;			
		}
	}
}