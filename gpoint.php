<? php
/ **
 * Uma classe PHP que converterá coordenadas de latitude e longitude em coordenadas UTM & Lambert Conic Conformal Norte / Leste.
 * 
* Encapsulando métodos para representar ponto geográfico na Terra em três sistemas de coordenadas diferentes. Lat / Long, UTM e Lambert Conic Conformal.
 * COPYRIGHT (c) 2005, 2006, 2007, 2008 BRENOR BROPHY
* Esse código foi adaptado por outras fontes e colocado de modo que eu achei melhor por aprendizados que obtive na linguagem e estruturação dos dados
 * /
classe gPoint
{
	 * @var array - format ("nome do elipsóide" => array (raio equatorial, quadrado da excentricidade))
	 * /
	public  static  $ ellipsoid = array (	
		"Airy" 					=> array ( 6377563 , 0,00667054 ),
		"Australian National" 	=> array 	( 6378160 , 0,006694542 ),
		"Bessel 1841" 			=> matriz 	( 6377397 , 0,006674372 ),
		"Bessel 1841 Nâmbia" 	=> matriz 	( 6377484 , 0,006674372 ),
		"Clarke 1866" 			=> array 	( 6378206 , 0,006768658 ),
		"Clarke 1880" 			=> array 	( 6378249 , 0,006803511 ),
		"Everest" 				=> matriz 	( 6377276 , 0,006637847 ),
		"Fischer 1960 Mercury" 	=> matriz ( 6378166 , 0,006693422 ),
		"Fischer 1968" 			=> matriz ( 6378150 , 0,006693422 ),
		"GRS 1967" 				=> matriz 	( 6378160 , 0,006694605 ),
		"GRS 1980" 				=> matriz 	( 6.378.137 , 0,00669438 ),
		"Helmert 1906" 			=> matriz 	( 6378200 , 0,006693422 ),
		"Hough" 					=> matriz 	( 6378270 , 0,00672267 ),
		"Internacional" 			=> matriz 	( 6378388 , 0,00672267 ),
		"Krassovsky" 			=> matriz 	( 6378245 , 0,006693422 ),
		"Modificado Airy" 			=> array 	( 6377340 , 0,00667054 ),
		"Everest modificado" 		=> matriz 	( 6377304 , 0,006637847 ),
		"Fischer modificado 1960" 	=> matriz 	( 6378155 , 0,006693422 ),
		"Sul-americano 1969" 	=> matriz 	( 6378160 , 0,006694542 ),
		"WGS 60" 				=> matriz ( 6378165 , 0,006693422 ),
		"WGS 66" 				=> matriz ( 6378145 , 0,006694542 ),
		"WGS 72" 				=> matriz ( 6378135 , 0,006694318 ),
		"WGS 84" 				=> matriz ( 6378137 , 0,00669438 ),
		// Nomes alternativos, adicionados para fácil compatibilidade por hd@onlinecity.dk
		"ED50" 					=> array 	( 6378388 , 0,00672267 ), // Elipsóide Internacional
		"EUREF89" 				=> matriz 	( 6378137 , 0,00669438 ), // Max desvio do WGS 84 é de 40 cm / km Ver (na dinamarquesa) http://www2.kms.dk/C1256AED004E87BA/(AllDocsByDocId)/3382517647F695C9C1256BC700265CE7
		"ETRS89" 				=> matriz 	( 6.378.137 , 0,00669438 )   // o mesmo que EUREF89
	);
	// Propriedades
	protegido  $ a ;									// Raio Equatorial
	protegido  $ e2 ;									// Quadrado de excentricidade
	protegido  $ datum ;								// dado selecionado
	protegido  $ Xp , $ Yp ;								// localização do pixel X, Y
	protegido  $ lat , $ long ;							// Latitude e longitude do ponto
	protegido  $ utmNorthing , $ utmEasting , $ utmZone ;	// Coordenadas UTM do ponto
	protegido  $ lccNorthing , $ lccEasting ;			// Coordenadas de Lambert do ponto
	protegido  $ falseNorthing , $ falseEasting ;		// Coordenadas de origem para a projeção de Lambert
	protegido  $ latOfOrigin ;							// Para projeção de Lambert
	protegido  $ longOfOrigin ;						// Para projeção de Lambert
	protegido  $ firstStdParallel ;					// Para projeção lambert
	protegido  $ secondStdParallel ;					// Para projeção lambert
	/ **
	 * Constrói o objeto e define o datum
	 * 
	 * @param string $ datum
	 * /
	 função  pública __construct ( $ datum = 'WGS 84' )			 // O dado padrão é WGS 84
	{
		$ this -> a = self :: $ elipsoid [ $ datum ] [ 0 ];		// Definir raio equatorial de referência
		$ this -> e2 = self :: $ elipsóide [ $ datum ] [ 1 ];	// Definir datum Quadrado de excentricidade
		$ this -> datum = $ datum ;						// Salve o datum
	}
	/ **
	 * Defina o datum
	 * 
	 * @param string $ datum
	 * /
	public  function  setDatum ( $ datum = 'WGS 84' )
	{
		$ this -> a = self :: $ elipsoid [ $ datum ] [ 0 ];		// Definir raio equatorial de referência
		$ this -> e2 = self :: $ elipsóide [ $ datum ] [ 1 ];	// Definir datum Quadrado de excentricidade
		$ this -> datum = $ datum ;						// Salve o datum
	}
	/ **
	 * Definir pixel X e Y do ponto (usado se estiver sendo desenhado em uma imagem)
	 * 
	 * @param inteiro $ x
	 * @param inteiro $ y
	 * /
	 função  pública setXY ( $ x , $ y )
	{
		$ this -> Xp = $ x ; $ this -> Yp = $ y ;
	}
	/ **
	 * Obtenha a localização de X pixels
	 * /
	public  function  Xp () {
		return  $ this -> Xp ;
	}
	/ **
	 * Obtenha a localização do pixel Y
	 * /
	public  function  Yp () {
		return  $ this -> Yp ;
	}
	/ **
	 * Definir / Obter / Saída Longitude e Latitude do ponto
	 * @param float $ long
	 * @param float $ lat
	 * /
	 função  pública setLongLat ( $ long , $ lat )
	{
		$ this -> long = $ long ;
		$ this -> lat = $ lat ;
	}
	/ **
	 * Obter latitude
	 * 
	 * /
	public  function  Lat () {
		return  $ this -> lat ;
	}
	/ **
	 * Obtenha longitude
	 * 
	 * /
	public  function  Long () {
		return  $ this -> long ;
	}
	/ **
	 * Imprimir latitude / longitude
	 * 
	 * /
	public  function  printLatLong () {
		printf ( "Latitude:% 1.5f Longitude:% 1.5f" , $ this -> lat , $ this -> long );
	}
	/ **
	 * Definir Coordenadas Transversais de Mercator Universal
	 * 
	 * @param integer $ easting
	 * @param integer $ northing
	 * @param string $ zone
	 * /
	public  function  setUTM ( $ easting , $ northing , $ zone = '' )	 // A zona é opcional
	{
		$ Presente -> utmNorthing = $ northing ;
		$ this -> utmEasting = $ easting ;
		$ this -> utmZone = $ zona ;
	}
	/ **
	 * Obtenha utm northing
	 * 
	 * /
	 função  pública N () {
		return  $ this -> utmNorthing ;
	}
	/ **
	 * Obtenha utm easting
	 * 
	 * /
	 função  pública E () {
		return  $ this -> utmEasting ;
	}
	/ **
	 * Obter zona utm
	 * /
	 função  pública Z () {
		return  $ this -> utmZone ;
	}
	/ **
	 * Imprimir coordenadas UTM
	 * 
	 * /
	public  function  printUTM () {
		print ( "Northing:" . ( int ) $ this -> utmNorthing . ", Easting:" . ( int ) $ this -> utmEasting . ", Zone:" . $ this -> utmZone );
	}
	/ **
	 * Defina as coordenadas Lambert
	 * 
	 * @param integer $ northing
	 * @param integer $ easting
	 * /
	público  função  setLambert ( $ northing , $ easting )
	{
		$ Presente -> lccNorthing = $ northing ;
		$ this -> lccEasting = $ easting ;
	}
	/ **
	 * Obtenha lccNorthing
	 * /
	public  function  lccN () {
		return  $ this -> lccNorthing ;
	}
	/ **
	 * Obtenha lccEasting
	 * /
	public  function  lccE () {
		return  $ this -> lccEasting ;
	}
	/ **
	 * Imprimir coordenadas Lambert
	 * 
	 * /
	public  function  printLambert () {
		print ( "Northing:" . ( int ) $ this -> lccNorthing . ", Easting:" . ( int ) $ this -> lccEasting );
	}
	/ **
	 * Converta Longitude / Latitude em UTM
	 * 
	 * Equações do Boletim 1532 do USGS 
	 * As longitudes leste são positivas, as longitudes oeste são negativas. 
	 * As latitudes norte são positivas, as latitudes sul são negativas
	 * Latitude e longitude estão em graus decimais. Se você passar um valor de Longitude para a função como $ LongOrigin
	 * então essa é a Longitude de Origem que será usada para a projeção. Se um valor NULL for passado para $ LongOrigin
	 * então as coordenadas UTM padrão são calculadas.
	 * 
	 * @param float $ LongOrigin
	 * /
	 função  pública convertLLtoTM ( $ LongOrigin )
	{
		$ k0 = 0,9996 ;
		$ falseEasting = 0,0 ;
		// Certifique-se de que a longitude esteja entre -180,00 .. 179,9
		$ LongTemp = ( $ this -> long + 180 ) - ( inteiro ) (( $ this -> long + 180 ) / 360 ) * 360 - 180 ; // -180,00 .. 179,9;
		$ LatRad = deg2rad ( $ this -> lat );
		$ LongRad = deg2rad ( $ LongTemp );
		if (! $ LongOrigin ) { // Faça uma conversão UTM padrão - então descubra em qual zona o ponto está
			$ ZoneNumber = ( inteiro ) (( $ LongTemp + 180 ) / 6 ) + 1 ;
			if ( $ this -> lat > = 56,0 && $ this -> lat < 64,0 && $ LongTemp > = 3,0 && $ LongTemp < 12,0 ) $ ZoneNumber = 32 ;
			// Zonas especiais para Svalbard
			if ( $ this -> lat > = 72,0 && $ this -> lat < 84,0 ) {
				if ( $ LongTemp > = 0,0   && $ LongTemp <   9,0 ) {
					$ ZoneNumber = 31 ;	
				} else  if ( $ LongTemp > = 9.0   && $ LongTemp < 21.0 ) {
					$ ZoneNumber = 33 ;	
				} else  if ( $ LongTemp > = 21,0 && $ LongTemp < 33,0 ) {
					$ ZoneNumber = 35 ;	
				} else  if ( $ LongTemp > = 33,0 && $ LongTemp < 42,0 ) {
					$ ZoneNumber = 37 ;	
				}
			}
			$ LongOrigin = ( $ ZoneNumber - 1 ) * 6 - 180 + 3 ;  // + 3 coloca a origem no meio da zona
			// calcula a zona UTM a partir da latitude e longitude
			$ this -> utmZone = sprintf ( "% d% s" , $ ZoneNumber , $ this -> UTMLetterDesignator ());
			// Também precisamos definir o valor falso para leste, ajustar a coordenada para leste UTM
			$ falseEasting = 500000,0 ;
		}
		$ LongOriginRad = deg2rad ( $ LongOrigin );
		$ eccPrimeSquared = ( $ this -> e2 ) / ( 1 - $ this -> e2 );
		$ N = $ this -> a / sqrt ( 1 - $ this -> e2 * sin ( $ LatRad ) * sin ( $ LatRad ));
		$ T = tan ( $ LatRad ) * tan ( $ LatRad );
		$ C = $ eccPrimeSquared * cos ( $ LatRad ) * cos ( $ LatRad );
		$ A = cos ( $ LatRad ) * ( $ LongRad - $ LongOriginRad );
		$ M = $ this -> a * (( 1 	- $ this -> e2 / 4 		- 3 * $ this -> e2 * $ this -> e2 / 64 	- 5 * $ this -> e2 * $ this -> e2 * $ this -> e2 / 256 ) * $ LatRad 
							- ( 3 * $ isto -> e2 / 8 	+ 3 * $ isto -> e2 * $ isto -> e2 / 32 	+ 45 * $ isto -> e2 * $ isto -> e2 * $ isto -> e2 / 1024 ) * sin ( 2 * $ LatRad )
												+ ( 15 * $ this -> e2 * $ this -> e2 / 256 + 45 * $ this -> e2 * $ this -> e2 * $ this -> e2 / 1024 ) * sin ( 4 * $ LatRad )
												- ( 35 * $ this -> e2 * $ this -> e2 * $ this -> e2 / 3072 ) * sin ( 6 * $ LatRad ));
	
		$ this -> utmEasting = ( $ k0 * $ N * ( $ A + ( 1 - $ T + $ C ) * $ A * $ A * $ A / 6
						+ ( 5 - 18 * $ T + $ T * $ T + 72 * $ C - 58 * $ eccPrimeSquared ) * $ A * $ A * $ A * $ A * $ A / 120 )
						+ $ falseEasting );
		$ this -> utmNorthing = ( $ k0 * ( $ M + $ N * tan ( $ LatRad ) * ( $ A * $ A / 2 + ( 5 - $ T + 9 * $ C + 4 * $ C * $ C ) * $ A * $ A * $ A * $ A / 24
					 + ( 61 - 58 * $ T + $ T * $ T + 600 * $ C - 330 * $ eccPrimeSquared ) * $ A * $ A * $ A * $ A * $ A * $ A / 720 )));
		if ( $ this -> lat < 0 ) $ this -> utmNorthing + = 10000000.0 ; // Deslocamento de 10000000 metros para o hemisfério sul
	}
	/ **
	 * Esta rotina determina o designador de letra UTM correto para a latitude fornecida
	 * retorna 'Z' se a latitude estiver fora dos limites UTM de 84N a 80S
	 * /
	 função  pública UTMLetterDesignator ()
	{	
		if (( 84 > = $ this -> lat ) && ( $ this -> lat > = 72 )) $ LetterDesignator = 'X' ;
		else  if (( 72 > $ this -> lat ) && ( $ this -> lat > = 64 )) $ LetterDesignator = 'W' ;
		else  if (( 64 > $ this -> lat ) && ( $ this -> lat > = 56 )) $ LetterDesignator = 'V' ;
		else  if (( 56 > $ this -> lat ) && ( $ this -> lat > = 48 )) $ LetterDesignator = 'U' ;
		else  if (( 48 > $ this -> lat ) && ( $ this -> lat > = 40 )) $ LetterDesignator = 'T' ;
		else  if (( 40 > $ this -> lat ) && ( $ this -> lat > = 32 )) $ LetterDesignator = 'S' ;
		else  if (( 32 > $ this -> lat ) && ( $ this -> lat > = 24 )) $ LetterDesignator = 'R' ;
		else  if (( 24 > $ this -> lat ) && ( $ this -> lat > = 16 )) $ LetterDesignator = 'Q' ;
		else  if (( 16 > $ this -> lat ) && ( $ this -> lat > = 8 )) $ LetterDesignator = 'P' ;
		else  if (( 8 > $ this -> lat ) && ( $ this -> lat > = 0 )) $ LetterDesignator = 'N' ;
		else  if (( 0 > $ this -> lat ) && ( $ this -> lat > = - 8 )) $ LetterDesignator = 'M' ;
		else  if ((- 8 > $ this -> lat ) && ( $ this -> lat > = - 16 )) $ LetterDesignator = 'L' ;
		else  if ((- 16 > $ this -> lat ) && ( $ this -> lat > = - 24 )) $ LetterDesignator = 'K' ;
		else  if ((- 24 > $ this -> lat ) && ( $ this -> lat > = - 32 )) $ LetterDesignator = 'J' ;
		else  if ((- 32 > $ this -> lat ) && ( $ this -> lat > = - 40 )) $ LetterDesignator = 'H' ;
		else  if ((- 40 > $ this -> lat ) && ( $ this -> lat > = - 48 )) $ LetterDesignator = 'G' ;
		else  if ((- 48 > $ this -> lat ) && ( $ this -> lat > = - 56 )) $ LetterDesignator = 'F' ;
		else  if ((- 56 > $ this -> lat ) && ( $ this -> lat > = - 64 )) $ LetterDesignator = 'E' ;
		else  if ((- 64 > $ this -> lat ) && ( $ this -> lat > = - 72 )) $ LetterDesignator = 'D' ;
		else  if ((- 72 > $ this -> lat ) && ( $ this -> lat > = - 80 )) $ LetterDesignator = 'C' ;
		else  $ LetterDesignator = 'Z' ; // Isso é um sinalizador de erro para mostrar que o Latitude está fora dos limites UTM
		return ( $ LetterDesignator );
	}
	/ **
	 * Converta UTM para Longitude / Latitude
	 *
	 * Equações do Boletim 1532 do USGS 
	 * As longitudes leste são positivas, as longitudes oeste são negativas. 
	 * As latitudes norte são positivas, as latitudes sul são negativas
	 * Latitude e longitude estão em graus decimais. 
	 *
	 * @param float $ LongOrigin
	 * /
	public  function  convertTMtoLL ( $ LongOrigin = null )
	{
		$ k0 = 0,9996 ;
		$ e1 = ( 1 - sqrt ( 1 - $ this -> e2 )) / ( 1 + sqrt ( 1 - $ this -> e2 ));
		$ falseEasting = 0,0 ;
		$ y = $ this -> utmNorthing ;
		if (! $ LongOrigin ) { // É uma coordenada UTM que queremos converter
			sscanf ( $ this -> utmZone , "% d% s" , $ ZoneNumber , $ ZoneLetter );
			if ( $ ZoneLetter > = 'N' ) {
				$ Hemisfério Norte = 1 ; // o ponto está no hemisfério norte
			} else {
				$ Hemisfério Norte = 0 ; // ponto está no hemisfério sul
				$ y - = 10000000,0 ; // remove o deslocamento de 10.000.000 metros usado para o hemisfério sul
			}
			$ LongOrigin = ( $ ZoneNumber - 1 ) * 6 - 180 + 3 ;  // + 3 coloca a origem no meio da zona
			$ falseEasting = 500000,0 ;
		}
// $ y - = 10000000.0; // Descomente a linha para fazer com que as coordenadas LOCAIS retornem à hemesfera sul Lat / Long
		$ x = $ this -> utmEasting - $ falseEasting ; // remove 500.000 metros de deslocamento para longitude
		$ eccPrimeSquared = ( $ this -> e2 ) / ( 1 - $ this -> e2 );
		$ M = $ y / $ k0 ;
		$ mu = $ M / ( $ this -> a * ( 1 - $ this -> e2 / 4 - 3 * $ this -> e2 * $ this -> e2 / 64 - 5 * $ this -> e2 * $ this -> e2 * $ this -> e2 / 256 ));
		$ phi1Rad = $ mu 	+ ( 3 * $ e1 / 2 - 27 * $ e1 * $ e1 * $ e1 / 32 ) * sin ( 2 * $ mu )
					+ ( 21 * $ e1 * $ e1 / 16 - 55 * $ e1 * $ e1 * $ e1 * $ e1 / 32 ) * sin ( 4 * $ mu )
					+ ( 151 * $ e1 * $ e1 * $ e1 / 96 ) * sin ( 6 * $ mu );
		$ phi1 = rad2deg ( $ phi1Rad );
		$ N1 = $ this -> a / sqrt ( 1 - $ this -> e2 * sin ( $ phi1Rad ) * sin ( $ phi1Rad ));
		$ T1 = tan ( $ phi1Rad ) * tan ( $ phi1Rad );
		$ C1 = $ eccPrimeSquared * cos ( $ phi1Rad ) * cos ( $ phi1Rad );
		$ R1 = $ this -> a * ( 1 - $ this -> e2 ) / pow ( 1 - $ this -> e2 * sin ( $ phi1Rad ) * sin ( $ phi1Rad ), 1,5 );
		$ D = $ x / ( $ N1 * $ k0 );
		$ tlat = $ phi1Rad - ( $ N1 * tan ( $ phi1Rad ) / $ R1 ) * ( $ D * $ D / 2 - ( 5 + 3 * $ T1 + 10 * $ C1 - 4 * $ C1 * $ C1 - 9 * $ eccPrimeSquared ) * $ D * $ D * $ D* $ D / 24
						+ ( 61 + 90 * $ T1 + 298 * $ C1 + 45 * $ T1 * $ T1 - 252 * $ eccPrimeSquared - 3 * $ C1 * $ C1 ) * $ D * $ D * $ D * $ D * $ D * $ D / 720 );
		$ this -> lat = rad2deg ( $ tlat );
		$ tlong = ( $ D - ( 1 + 2 * $ T1 + $ C1 ) * $ D * $ D * $ D / 6 + ( 5 - 2 * $ C1 + 28 * $ T1 - 3 * $ C1 * $ C1 + 8 * $ eccPrimeSquared + 24 * $ T1 * $ T1)
						* $ D * $ D * $ D * $ D * $ D / 120 ) / cos ( $ phi1Rad );
		$ this -> long = $ LongOrigin + rad2deg ( $ tlong );
	}
	/ **
	 * Configure uma Projeção Conformal Cônica Lambert
	 *
	 * falseEasting e falseNorthing são apenas um deslocamento em metros adicionado ao final
	 * coordenada calculada.
	 *
	 * longOfOrigin e LatOfOrigin são a latitude "central" e a longitude do
	 * área sendo projetada. Todas as coordenadas serão calculadas em metros relativos
	 * até este ponto na terra.
	 *
	 * firstStdParallel e secondStdParallel são as duas linhas de longitude (que
	 * se eles correm de leste a oeste) que definem onde o "cone" intercepta a terra.
	 * Simplesmente, eles devem colocar entre parênteses a área que está sendo projetada. 
	 *
	 * @param integer $ falseEasting
	 * @param integer $ falseNorthing
	 * @param float $ longOfOrigin
	 * @param float $ latOfOrigin
	 * @param float $ firstStdParallel
	 * @param float $ secondStdParallel
	 * /
	public  function  configLambertProjection ( $ falseEasting , $ falseNorthing , $ longOfOrigin , $ latOfOrigin , $ firstStdParallel , $ secondStdParallel )
	{
		$ this -> falseEasting = $ falseEasting ;
		$ this -> falseNorthing = $ falseNorthing ;
		$ this -> longOfOrigin = $ longOfOrigin ;
		$ this -> latOfOrigin = $ latOfOrigin ;
		$ this -> firstStdParallel = $ firstStdParallel ;
		$ this -> secondStdParallel = $ secondStdParallel ;
	}
	/ **
	 * Converta Longitude / Latitude para Lambert Conic Easting / Northing
	 *
	 * /
	 função  pública convertLLtoLCC ()
	{
		$ e = sqrt ( $ this -> e2 );
		$ phi  	= deg2rad ( $ this -> lat );						// Latitude para converter
		$ phi1 	= deg2rad ( $ this -> firstStdParallel );			// Latitude do primeiro paralelo padrão
		$ phi2 	= deg2rad ( $ this -> secondStdParallel );		// Latitude do 2º paralelo padrão
		$ lamda 	= deg2rad ( $ this -> longo );						// Lonitude para converter
		$ phio 	= deg2rad ( $ this -> latOfOrigin );				// Latitude de Origem
		$ lamdao 	= deg2rad ( $ this -> longOfOrigin );				// Longitude de origem
		$ m1 = cos ( $ phi1 ) / sqrt (( 1 - $ this -> e2 * sin ( $ phi1 ) * sin ( $ phi1 )));
		$ m2 = cos ( $ phi2 ) / sqrt (( 1 - $ this -> e2 * sin ( $ phi2 ) * sin ( $ phi2 )));
		$ t1 = tan (( pi () / 4 ) - ( $ phi1 / 2 )) / pow ((( 1 - $ e * sin ( $ phi1 )) / ( 1 + $ e * sin ( $ phi1 ))) , $ e / 2 );
		$ t2 = tan (( pi () / 4 ) - ( $ phi2 / 2 )) / pow ((( 1 - $ e * sin ( $ phi2 )) / ( 1 + $ e * sin ( $ phi2 ))) , $ e / 2 );
		$ to = tan (( pi () / 4 ) - ( $ phio / 2 )) / pow ((( 1 - $ e * sin ( $ phio )) / ( 1 + $ e * sin ( $ phio ))) , $ e / 2 );
		$ t   = tan (( pi () / 4 ) - ( $ phi / 2 )) / pow ((( 1 - $ e * sin ( $ phi )) / ( 1 + $ e * sin ( $ phi ))) , $ e / 2 );
		$ n 	= ( log ( $ m1 ) - log ( $ m2 )) / ( log ( $ t1 ) - log ( $ t2 ));
		$ F 	= $ m1 / ( $ n * pow ( $ t1 , $ n ));
		$ rf 	= $ this -> a * $ F * pow ( $ to , $ n );
		$ r 	= $ this -> a * $ F * pow ( $ t , $ n );
		$ theta = $ n * ( $ lamda - $ lamdao );
		$ this -> lccEasting = $ this -> falseEasting + $ r * sin ( $ theta );
		$ this -> lccNorthing = $ this -> falseNorthing + $ rf - $ r * cos ( $ theta );
	}
	/ **
	 * Converta Leste / Norte em uma projeção Lambert Conic para Longitude / Latitude
	 *
	 * Esta rotina irá converter uma coordenada Lambert Northing / Easting em uma
	 * Coordenadas de latitude / longitude. A função configLambertProjection () deve
	 * foram chamados antes deste para configurar os parâmetros específicos para o
	 * projeção. Os parâmetros de Norte / Leste estão em metros (porque o datum
	 * usado está em metros) e é relativo a falseNorthing / falseEasting
	 * coordenar. Que por sua vez é relativo ao Lat / Long de origem. A fórmula
	 * foram obtidos no URL http://www.ihsenergy.com/epsg/guid7_2.html. Código
	 * foi escrito por Brenor Brophy, brenor@sbcglobal.net
	 * /
	public  function  convertLCCtoLL ()
	{
		$ e = sqrt ( $ e2 );
		$ phi1 	= deg2rad ( $ this -> firstStdParallel );			// Latitude do primeiro paralelo padrão
		$ phi2 	= deg2rad ( $ this -> secondStdParallel );		// Latitude do 2º paralelo padrão
		$ phio 	= deg2rad ( $ this -> latOfOrigin );				// Latitude de Origem
		$ lamdao 	= deg2rad ( $ this -> longOfOrigin );				// Longitude de origem
		$ E 		= $ this -> lccEasting ;
		$ N 		= $ this -> lccNorthing ;
		$ Ef 		= $ this -> falseEasting ;
		$ Nf 		= $ this -> falseNorthing ;
		$ m1 = cos ( $ phi1 ) / sqrt (( 1 - $ this -> e2 * sin ( $ phi1 ) * sin ( $ phi1 )));
		$ m2 = cos ( $ phi2 ) / sqrt (( 1 - $ this -> e2 * sin ( $ phi2 ) * sin ( $ phi2 )));
		$ t1 = tan (( pi () / 4 ) - ( $ phi1 / 2 )) / pow ((( 1 - $ e * sin ( $ phi1 )) / ( 1 + $ e * sin ( $ phi1 ))) , $ e / 2 );
		$ t2 = tan (( pi () / 4 ) - ( $ phi2 / 2 )) / pow ((( 1 - $ e * sin ( $ phi2 )) / ( 1 + $ e * sin ( $ phi2 ))) , $ e / 2 );
		$ to = tan (( pi () / 4 ) - ( $ phio / 2 )) / pow ((( 1 - $ e * sin ( $ phio )) / ( 1 + $ e * sin ( $ phio ))) , $ e / 2 );
		$ n 	= ( log ( $ m1 ) - log ( $ m2 )) / ( log ( $ t1 ) - log ( $ t2 ));
		$ F 	= $ m1 / ( $ n * pow ( $ t1 , $ n ));
		$ rf 	= $ this -> a * $ F * pow ( $ to , $ n );
		$ r_ 	= sqrt ( pow (( $ E - $ Ef ), 2 ) + pow (( $ rf - ( $ N - $ Nf )), 2 ));
		$ t_ 	= pow ( $ r_ / ( $ this -> a * $ F ), ( 1 / $ n ));
		$ theta_ = atan (( $ E - $ Ef ) / ( $ rf - ( $ N - $ Nf )));
		$ lamda 	= $ theta_ / $ n + $ lamdao ;
		$ phi0 	= ( pi () / 2 ) - 2 * atan ( $ t_ );
		$ phi1 	= ( pi () / 2 ) - 2 * atan ( $ t_ * pow ((( 1 - $ e * sin ( $ phi0 )) / ( 1 + $ e * sin (phi0))), $ e / 2 ));
		$ phi2 	= ( pi () / 2 ) - 2 * atan ( $ t_ * pow ((( 1 - $ e * sin ( $ phi1 )) / ( 1 + $ e * sin (phi1))), $ e / 2 ));
		$ phi 	= ( pi () / 2 ) - 2 * atan ( $ t_ * pow ((( 1 - $ e * sin ( $ phi2 )) / ( 1 + $ e * sin (phi2))), $ e / 2 ));
		
		$ this -> lat  	= rad2deg ( $ phi );
		$ this -> long = rad2deg ( $ lamda );
	}
	/ **
	 * Esta é uma função útil que retorna a distância do Grande Círculo do gPoint para outra coordenada Long / Lat
	 * 
	 * O resultado é retornado em metros
	 * @param float $ lon1
	 * @param float $ lat1
	 * /
	public  function  distanceFrom ( $ lon1 , $ lat1 )
	{ 
		$ lon2 = deg2rad ( $ this -> Long ()); $ lat2 = deg2rad ( $ this -> Lat ());
		$ theta = $ lon2 - $ lon1 ;
		$ dist = acos ( sin ( $ lat1 ) * sin ( $ lat2 ) + cos ( $ lat1 ) * cos ( $ lat2 ) * cos ( $ theta ));
// Fórmula alternativa mais precisa para distâncias curtas
// $ dist = 2 * asin (sqrt (pow (sin (($ lat1- $ lat2) / 2), 2) + cos ($ lat1) * cos ($ lat2) * pow (sin (($ lon1- $ lon2) / 2), 2)));
		return ( $ dist * 6366710 ); // de http://williams.best.vwh.net/avform.htm#GCF
	}
	/ **
	 * Esta função também calcula a distância entre dois pontos. Neste caso, ele apenas usa a teormita de Pitágoras usando as coordenadas da TM.
	 * @param gPoint $ pt
	 * /
	public  function  distanceFromTM (& $ pt )
	{ 
		$ E1 = $ pt -> E (); 	$ N1 = $ pt -> N ();
		$ E2 = $ this -> E (); 	$ N2 = $ this -> N ();
 
		$ dist = sqrt ( pow (( $ E1 - $ E2 ), 2 ) + pow (( $ N1 - $ N2 ), 2 ));
		return  $ dist ;
	}
	/ **
	 * $ rX & $ rY são as coordenadas de pixel que correspondem ao Norte / Leste
	 coordenada * ($ rE / $ rN) é para esta coordenada que o ponto será
	 * georreferenciado. O $ LongOrigin é necessário para garantir que o Leste / Norte
	 * as coordenadas do ponto são convertidas corretamente.
	 * @param integer $ rX
	 * @param inteiro $ rY
	 * @param inteiro $ rE
	 * @param inteiro $ rN
	 * @param integer $ Scale
	 * @param float $ LongOrigin
	 * /
	 função  pública gRef ( $ rX , $ rY , $ rE , $ rN , $ Scale , $ LongOrigin )
	{
		$ this -> convertLLtoTM ( $ LongOrigin );
		$ x = (( $ this -> E () - $ rE ) / $ Scale )		 // O leste em metros vezes a escala para obter pixels
												// é relativo ao centro da imagem, então ajuste para
			+ ( $ rX );							// a coordenada esquerda.
		$ y = $ rY -  							 // Ajustar à coordenada inferior.
			(( $ rN - $ this -> N ()) / $ Escala );		// O norte em metros
												// em relação ao equador. Subtraia o ponto central ao norte
												// para obter em relação ao centro da imagem e converter metros em pixels
		$ this -> setXY (( int ) $ x , ( int ) $ y );			// Salve o resultado georreferenciado.
	}
}