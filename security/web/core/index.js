const dominio = "http://localhost/lis";

//links a serem incluidos na pagina
const scriptsGlobais = ["/framework/jquery-3.6.0.js", "/framework/materialize/js/materialize.js", "/framework/vue.global.js", "/js/global/variaveis.js", "/js/global/funcoes.js", "/js/global/carregando.js"];

// const stylesGlobais = [URLS.dominioFramework + "materialize/css/materialize.css", URLS.dominioAssets + "styles/carregando.css"];
const stylesGlobais = ["/framework/materialize/css/materialize.css", "https://fonts.googleapis.com/icon?family=Material+Icons", "/css/global/carregando.css"];

//funções ==================================================

/**
 *
 * @param {array} urls - array das urls
 * @param {string} tipo - tipo de arquivo que será adicionado na tela
 * Adiciona as tags de script e link a tela
 */
function incluiScript(links, tipo) {
	switch (tipo) {
		case "js":
			links.forEach((url) => {
				var script = document.createElement("script");
				script.setAttribute("src", validaUrl(url));
				document.querySelector("body").appendChild(script);
			});
			break;
		case "css":
			links.forEach((url) => {
				var style = document.createElement("link");
				style.setAttribute("rel", "stylesheet");
				style.setAttribute("href", validaUrl(url));
				document.querySelector("head").appendChild(style);
			});
			break;
	}
}

/**
 * Função para pegar a url correta
 * @param {string} url string da url
 * @returns {string} - url a ser usada
 */
function validaUrl(url) {
	if (url.substr(0, 1) == "/") {
		return dominio + url;
	}

	return url;
}

/**
 * Função para criar o elemento "carregando" na tela
 */
function criarCarregando() {
	//cria o elemento de carregando
	var carregando = document.createElement("carregando");
	carregando.setAttribute("class", "scale-transition scale-in");
	document.querySelector("html").appendChild(carregando);

	//insere a imagem no mesmo
	var img = document.createElement("img");
	img.setAttribute("class", "materialboxed");
	img.setAttribute("src", validaUrl("/midia/global/Carregando.gif"));
	//coloca a imagem dentro do carregando
	document.querySelector("carregando").appendChild(img);

	//some com o body
	var body = document.querySelector("body");
	body.setAttribute("class", "scale-transition scale-out");
	body.setAttribute("style", "display: none;");
}

/**
 * Função para adicionar os metadados do arquivo
 */
function createMeta() {
	//os elementos meta
	var meta = document.createElement("meta");
	meta.setAttribute("name", "viewport");
	meta.setAttribute("content", "width=device-width, initial-scale=1.0");
	document.querySelector("head").prepend(meta);

	var meta = document.createElement("meta");
	meta.setAttribute("http-equiv", "X-UA-Compatible");
	meta.setAttribute("content", "IE=edge");
	document.querySelector("head").prepend(meta);

	var meta = document.createElement("meta");
	meta.setAttribute("charset", "utf-8");
	document.querySelector("head").prepend(meta);

	var html = document.querySelector("html");
	html.setAttribute("lang", "pt-br");
}

/**
 * Função chamada para iniciar o framework
 */
function init() {
	criarCarregando();

	incluiScript(["/css/global/variaveis.css", "/coreCss"], "css");

	document.querySelector("carregando img").onload = function () {
		if (document.querySelector("nav") == null && Lis.nav != false) {
			Lis.createComponent("nav", "body");
		}

		createMeta();

		incluiScript(stylesGlobais, "css");
		incluiScript(scriptsGlobais, "js");

		//incluindo arquivos css do usuario
		if (Lis.styles) {
			incluiScript(Lis.styles, "css");
		}
		//incluindo scripts dos usuarios
		if (Lis.scripts) {
			incluiScript(Lis.scripts, "js");
		}

		if (Lis && typeof Lis.init === "function") {
			//aguarda o carregamento das paginas e executa o init
			document.querySelector("body").onload = function () {
				Lis.init();

				if (Lis.nav != false) {
					//valida se o vue existe
					if (typeof window.Vue !== "undefined") {
						window.initVueDefault(document.querySelector("nav"));
					}
				}

				setTimeout(function () {
					//apos o carregamento some a tela de carregamento
					Lis.carregandoHide();
				}, 300);
			};
		}
	};

	// document.querySelector("body").onerror = function (erro) {
	//     if (window.location.href != URLS.dominioErros + "700.html") {
	//         window.location.href = URLS.dominioErros + "700.html";
	//     }
	// };
}

//incluindo funções na Lis ====================================

/**
 *
 * @param {string} url Url destino da solicitação
 * @param {boolean} assincrona função assincrona ? - padrão false
 */
Lis.get = function (url, assincrona = false) {
	var xhttp = new XMLHttpRequest();
	xhttp.open("GET", url, assincrona);
	xhttp.send();
	return xhttp.responseText;
};

/**
 *
 * @param {string} url link para a requisição post
 * @param {obj} dados dados a serem enviados para o servidor
 * @param {boolean} assincrona função assincrona ?
 * @returns resposta do post
 */
Lis.post = async function (url, dados) {
	const data = await fetch(url, {
		body: JSON.stringify(dados),
		method: "POST",
		headers: {
			"Content-Type": "application/json",
		},
		data: dados,
	});

	var texto = await data.text();

	return texto;
};

/**
 * Esconde a tela de carregando
 */
Lis.carregandoHide = function () {
	const carregando = document.querySelector("carregando");
	const pagina = document.querySelector("body");

	carregando.classList.remove("scale-in");
	carregando.classList.add("scale-out");
	setTimeout(function () {
		carregando.style.display = "none";
		pagina.style.display = null;
		setTimeout(function () {
			pagina.classList.remove("scale-out");
			pagina.classList.add("scale-in");
		}, 200);
	}, 500);
};

/**
 * Exibe a tela de carregando
 */
Lis.carregandoShow = function () {
	const carregando = document.querySelector("carregando");
	const pagina = document.querySelector("body");

	pagina.classList.remove("scale-in");
	pagina.classList.add("scale-out");
	setTimeout(function () {
		pagina.style.display = "none";
		carregando.style.display = null;
		setTimeout(function () {
			carregando.classList.remove("scale-out");
			carregando.classList.add("scale-in");
		}, 200);
	}, 500);
};

/**
 *
 * @param {string} component Nome do component a ser colocado
 * @param {string} element local onde o elemento será criado
 */
Lis.createComponent = function (component, element) {
	var elemento = document.createElement(component);
	document.querySelector(element).prepend(elemento);
	elemento.innerHTML = Lis.get(validaUrl("/component/" + component + ".html"), false);
};

/**
 * Função a ser aplicada nos forms, para que consiga
 * @param {string} element seletor do elemento do form
 */
Lis.form = (element) => {
	document.querySelector(element).addEventListener(
		"submit",
		async function (event) {
			event.preventDefault();
			const data = new FormData(event.target);
			const value = Object.fromEntries(data.entries());

			var url = validaUrl("/server/" + document.querySelector("form").action.replace(location.origin, "").replace("/", ""));

			if (document.querySelector(element).method == "post") {
				await Lis.post(url, value);
			}
		},
		true
	);
};

//iniciando a pagina ===========================================
init();
