// Contur simplificat al României (graniță națională, proiecție echirectangulară
// centrată pe latitudinea medie a țării) și coordonatele orașelor mari în același
// sistem de coordonate SVG, calculate cu aceeași proiecție — astfel încât punctele
// cad corect pe hartă.

export const ROMANIA_VIEWBOX = '0 0 540 386.5';

export const ROMANIA_PATH =
	'M 152.38,45.9 L 175.32,29.52 L 208.21,37.99 L 242.29,38.27 L 266.97,56.96 L 285.12,45.22 ' +
	'L 324.36,37.87 L 337.72,20.0 L 360.15,20.01 L 376.35,27.46 L 392.82,50.13 L 409.68,82.37 ' +
	'L 440.35,127.84 L 442.05,161.39 L 436.43,194.04 L 445.95,228.93 L 469.67,243.02 L 494.65,230.71 ' +
	'L 518.76,243.84 L 520.0,263.56 L 494.22,280.01 L 478.08,272.85 L 463.21,365.09 L 431.95,357.06 ' +
	'L 393.27,329.26 L 330.69,347.04 L 304.33,366.54 L 226.27,362.52 L 185.43,350.59 L 164.83,356.19 ' +
	'L 149.54,324.76 L 139.8,311.43 L 152.12,298.53 L 139.01,289.01 L 122.32,306.14 L 91.33,283.93 ' +
	'L 87.15,252.41 L 54.77,234.43 L 48.81,210.1 L 20.0,180.06 L 62.62,165.64 L 94.75,113.79 ' +
	'L 119.91,61.93 L 152.38,45.9 Z';

// Orașe mari — cheia e numele normalizat (fără diacritice, minuscule).
const CITY_COORDS = {
	bucuresti: [332.68, 310.09],
	'cluj-napoca': [200.91, 130.84],
	timisoara: [72.54, 209.0],
	iasi: [412.35, 101.23],
	constanta: [467.29, 330.5],
	craiova: [210.02, 317.47],
	brasov: [306.03, 217.12],
	galati: [435.76, 233.96],
	ploiesti: [328.45, 270.74],
	oradea: [110.76, 109.84],
	braila: [431.25, 245.68],
	arad: [78.05, 175.54],
	pitesti: [267.12, 277.23],
	sibiu: [227.59, 205.23],
	bacau: [375.85, 146.45],
	'targu mures': [250.47, 147.55],
	'baia mare': [197.97, 63.14],
	buzau: [370.85, 254.69],
	botosani: [362.79, 56.32],
	'satu mare': [161.69, 52.81],
	'ramnicu valcea': [240.54, 258.26],
	'drobeta-turnu severin': [149.47, 294.79],
	suceava: [340.62, 63.97],
	'piatra neamt': [346.94, 118.89],
	'targu jiu': [182.47, 263.57],
	tulcea: [476.26, 252.56],
	focsani: [390.18, 212.99],
	bistrita: [247.33, 103.15],
	resita: [108.69, 243.35],
	slatina: [240.3, 309.67],
	calarasi: [397.91, 326.98],
	giurgiu: [325.63, 350.08],
	deva: [162.95, 199.09],
	zalau: [170.83, 99.01],
	'sfantu gheorghe': [315.92, 200.19],
	'miercurea ciuc': [316.7, 162.23],
	'alba iulia': [198.06, 184.7],
	vaslui: [419.13, 140.89],
	slobozia: [399.99, 299.6],
	alexandria: [291.71, 345.46],
	targoviste: [298.3, 271.15],
};

// Centrul fiecărui județ (orașul de reședință) — fallback când localitatea
// exactă nu e în lista de mai sus (ex. o comună din jurul unui oraș mare).
const COUNTY_COORDS = {
	AB: CITY_COORDS['alba iulia'],
	AR: CITY_COORDS.arad,
	AG: CITY_COORDS.pitesti,
	BC: CITY_COORDS.bacau,
	BH: CITY_COORDS.oradea,
	BN: CITY_COORDS.bistrita,
	BT: CITY_COORDS.botosani,
	BR: CITY_COORDS.braila,
	BV: CITY_COORDS.brasov,
	BZ: CITY_COORDS.buzau,
	CS: CITY_COORDS.resita,
	CL: CITY_COORDS.calarasi,
	CJ: CITY_COORDS['cluj-napoca'],
	CT: CITY_COORDS.constanta,
	CV: CITY_COORDS['sfantu gheorghe'],
	DB: CITY_COORDS.targoviste,
	DJ: CITY_COORDS.craiova,
	GL: CITY_COORDS.galati,
	GR: CITY_COORDS.giurgiu,
	GJ: CITY_COORDS['targu jiu'],
	HR: CITY_COORDS['miercurea ciuc'],
	HD: CITY_COORDS.deva,
	IL: CITY_COORDS.slobozia,
	IS: CITY_COORDS.iasi,
	IF: CITY_COORDS.bucuresti,
	MM: CITY_COORDS['baia mare'],
	MH: CITY_COORDS['drobeta-turnu severin'],
	MS: CITY_COORDS['targu mures'],
	NT: CITY_COORDS['piatra neamt'],
	OT: CITY_COORDS.slatina,
	PH: CITY_COORDS.ploiesti,
	SM: CITY_COORDS['satu mare'],
	SJ: CITY_COORDS.zalau,
	SB: CITY_COORDS.sibiu,
	SV: CITY_COORDS.suceava,
	TR: CITY_COORDS.alexandria,
	TM: CITY_COORDS.timisoara,
	TL: CITY_COORDS.tulcea,
	VS: CITY_COORDS.vaslui,
	VL: CITY_COORDS['ramnicu valcea'],
	VN: CITY_COORDS.focsani,
	B: CITY_COORDS.bucuresti,
};

function normalize(str) {
	return (str || '')
		.toLowerCase()
		.normalize('NFD')
		.replace(/[\u0300-\u036f]/g, '')
		.trim();
}

/**
 * Întoarce coordonatele [x, y] pentru o localitate, cu fallback pe centrul
 * județului dacă localitatea exactă nu e cunoscută. Null dacă nu găsim nimic.
 */
export function pointFor(locality, county) {
	const cityMatch = CITY_COORDS[normalize(locality)];
	if (cityMatch) return cityMatch;

	const countyMatch = COUNTY_COORDS[(county || '').toUpperCase()];
	if (countyMatch) return countyMatch;

	return null;
}
