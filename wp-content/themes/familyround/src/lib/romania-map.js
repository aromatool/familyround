// Coordonate reale (longitudine, latitudine) pentru orașele mari și pentru
// centrul fiecărui județ — proiectate în pixeli SVG cu formula exactă din
// metadata FamilyRound_harta_Romaniei.svg (vezi harta-svg.js), ca punctele
// să cadă corect peste conturul oficial al hărții.

// Orașe mari — cheia e numele normalizat (fără diacritice, minuscule).
const CITY_LONLAT = {
	bucuresti: [26.1025, 44.4268],
	'cluj-napoca': [23.6236, 46.7712],
	floresti: [23.4899, 46.7439],
	timisoara: [21.2087, 45.7489],
	iasi: [27.6014, 47.1585],
	constanta: [28.635, 44.1598],
	craiova: [23.7949, 44.3302],
	brasov: [25.6012, 45.6427],
	galati: [28.0417, 45.4225],
	ploiesti: [26.0229, 44.9414],
	oradea: [21.9276, 47.0458],
	braila: [27.957, 45.2692],
	arad: [21.3123, 46.1866],
	pitesti: [24.8692, 44.8565],
	sibiu: [24.1256, 45.7983],
	bacau: [26.9146, 46.567],
	'targu mures': [24.5559, 46.5527],
	'baia mare': [23.5682, 47.6567],
	buzau: [26.8206, 45.1514],
	botosani: [26.669, 47.7458],
	'satu mare': [22.8858, 47.7917],
	'ramnicu valcea': [24.3691, 45.1047],
	'drobeta-turnu severin': [22.6558, 44.6269],
	suceava: [26.252, 47.6458],
	'piatra neamt': [26.3708, 46.9275],
	'targu jiu': [23.2767, 45.0352],
	tulcea: [28.8036, 45.1792],
	focsani: [27.1842, 45.6967],
	bistrita: [24.4969, 47.1333],
	resita: [21.8886, 45.2997],
	slatina: [24.3647, 44.4323],
	calarasi: [27.3297, 44.2058],
	giurgiu: [25.9699, 43.9037],
	deva: [22.9094, 45.8786],
	zalau: [23.0578, 47.1875],
	'sfantu gheorghe': [25.7872, 45.8642],
	'miercurea ciuc': [25.8019, 46.3606],
	'alba iulia': [23.57, 46.0667],
	vaslui: [27.7289, 46.6397],
	slobozia: [27.3689, 44.5639],
	alexandria: [25.3317, 43.9642],
	targoviste: [25.4557, 44.9361],
};

// Centrul (orașul de reședință) fiecărui județ — fallback când localitatea
// exactă nu e în lista de mai sus (ex. o comună din jurul unui oraș mare).
const COUNTY_LONLAT = {
	AB: CITY_LONLAT['alba iulia'],
	AR: CITY_LONLAT.arad,
	AG: CITY_LONLAT.pitesti,
	BC: CITY_LONLAT.bacau,
	BH: CITY_LONLAT.oradea,
	BN: CITY_LONLAT.bistrita,
	BT: CITY_LONLAT.botosani,
	BR: CITY_LONLAT.braila,
	BV: CITY_LONLAT.brasov,
	BZ: CITY_LONLAT.buzau,
	CS: CITY_LONLAT.resita,
	CL: CITY_LONLAT.calarasi,
	CJ: CITY_LONLAT['cluj-napoca'],
	CT: CITY_LONLAT.constanta,
	CV: CITY_LONLAT['sfantu gheorghe'],
	DB: CITY_LONLAT.targoviste,
	DJ: CITY_LONLAT.craiova,
	GL: CITY_LONLAT.galati,
	GR: CITY_LONLAT.giurgiu,
	GJ: CITY_LONLAT['targu jiu'],
	HR: CITY_LONLAT['miercurea ciuc'],
	HD: CITY_LONLAT.deva,
	IL: CITY_LONLAT.slobozia,
	IS: CITY_LONLAT.iasi,
	IF: CITY_LONLAT.bucuresti,
	MM: CITY_LONLAT['baia mare'],
	MH: CITY_LONLAT['drobeta-turnu severin'],
	MS: CITY_LONLAT['targu mures'],
	NT: CITY_LONLAT['piatra neamt'],
	OT: CITY_LONLAT.slatina,
	PH: CITY_LONLAT.ploiesti,
	SM: CITY_LONLAT['satu mare'],
	SJ: CITY_LONLAT.zalau,
	SB: CITY_LONLAT.sibiu,
	SV: CITY_LONLAT.suceava,
	TR: CITY_LONLAT.alexandria,
	TM: CITY_LONLAT.timisoara,
	TL: CITY_LONLAT.tulcea,
	VS: CITY_LONLAT.vaslui,
	VL: CITY_LONLAT['ramnicu valcea'],
	VN: CITY_LONLAT.focsani,
	B: CITY_LONLAT.bucuresti,
};

function normalize(str) {
	return (str || '')
		.toLowerCase()
		.normalize('NFD')
		.replace(/[\u0300-\u036f]/g, '')
		.trim();
}

/**
 * Întoarce [longitudine, latitudine] pentru o localitate, cu fallback pe
 * centrul județului dacă localitatea exactă nu e cunoscută. Null dacă nu
 * găsim nimic — nu inventăm o poziție.
 */
export function lonLatFor(locality, county) {
	const cityMatch = CITY_LONLAT[normalize(locality)];
	if (cityMatch) return cityMatch;

	const countyMatch = COUNTY_LONLAT[(county || '').toUpperCase()];
	if (countyMatch) return countyMatch;

	return null;
}
