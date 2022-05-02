try {
	//define as variaveis globais
	var VarsGlobal = {
		url: 'http://localhost'
	};

	//links a serem incluidos na pagina
	const scriptsGlobais = [
		"/framework/jquery-3.6.0.js", //jquery version 3.6.0
		"/framework/bootstrap-5.1.3-dist/js/bootstrap.js", //bootstrap version 5.1.3
		"/framework/vue.global.js", //vue version 3
		"/js/global/funcoes.js", //funcoes
		"/js/global/carregando.js", //carregando
	];

	const stylesGlobais = [
		"/framework/bootstrap-5.1.3-dist/css/bootstrap.css", //bootstrap version 5.1.3
		"https://fonts.googleapis.com/icon?family=Material+Icons", // google icons
	];

	//funções ==================================================

	/**
	 * Adiciona as tags de script e link a tela
	 *
	 * @param {array} links - Array de urls que serão importadas
	 * @param {string} tipo - tipo de arquivo que será adicionado na tela (css, js)
	 * @return {void} - Função não retorna dados
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
	 *
	 * @param {string} url - recebe a url
	 * @return {string} - url a ser usada
	 */
	function validaUrl(url) {
		if (url.substr(0, 1) == "/") {
			return VarsGlobal.url + url;
		}

		return url;
	}

	/**
	 * Função para criar o elemento "carregando" na tela
	 *
	 * @return {void} - Função não tem retorno
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

		incluiScript(["/css/global/carregando.css"], "css");
	}

	/**
	 * Função para adicionar os metadados do arquivo
	 *
	 * @return {void} - Função não tem retorno
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
	 *
	 * @return {void} - Função não tem retorno
	 */
	function init() {
		//seta as variavies globais
		VarsGlobal = JSON.parse(Lis.get(Lis.validaUrl(document.querySelector("#coreJs").src.replaceAll("coreJs", "varsApp")), false));

		criarCarregando();

		incluiScript(["/css/global/variaveis.css", "/coreCss"], "css");

		document.querySelector("carregando img").onload = function () {
			if (document.querySelector("nav") == null && Lis.nav != false) {
				Lis.createComponent("nav", "body");
			}

			createMeta();

			incluiScript(stylesGlobais, "css");
			incluiScript(scriptsGlobais, "js");

			incluiScript(["/css/global/variaveisBootstrap.css"], "css"); //arquivo para substituir variaveis do bootstrap

			//incluindo arquivos css do usuario
			if (Lis.styles) {
				incluiScript(Lis.styles, "css");
			}
			//incluindo scripts dos usuarios
			if (Lis.scripts) {
				incluiScript(Lis.scripts, "js");
			}

			if (Lis) {
				//aguarda o carregamento das paginas e executa o init
				document.querySelector("body").onload = function () {
					if (typeof Lis.init === "function") {
						Lis.init();
					}

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
	 * Adicionando a função valida url na lis
	 *
	 * @param {string} url- url que será montada
	 * @returns {string} - url a ser usada
	 */
	Lis.validaUrl = (url) => {
		return validaUrl(url);
	};

	/**
	 *
	 * @param {string} url - Url destino da solicitação
	 * @param {boolean} assincrona - função assincrona ? - padrão false
	 */
	Lis.get = function (url, assincrona = false) {
		var xhttp = new XMLHttpRequest();
		xhttp.open("GET", url, assincrona);
		xhttp.send();
		return xhttp.responseText;
	};

	/**
	 *
	 * @param {string} url -  link para a requisição post
	 * @param {obj} dados -  dados a serem enviados para o servidor
	 * @param {boolean} assincrona -  função assincrona ?
	 * @return resposta do post
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
	 *
	 * @return {void} - Função não tem retorno
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
	 *
	 * @return {void} - Função não tem retorno
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
	 *
	 * @return {void} - Função não tem retorno
	 */
	Lis.createComponent = function (component, element) {
		var elemento = document.createElement(component);
		document.querySelector(element).prepend(elemento);
		elemento.innerHTML = Lis.get(validaUrl("/components/" + component + ".html"), false);
	};

	/**
	 * Função a ser aplicada nos forms, para que consiga
	 * @param {string} element seletor do elemento do form
	 * @param {function} before Função a ser executada antes do envio
	 * @param {function} after Função a ser executada apos o envio
	 */
	Lis.form = (element, before, after) => {
		document.querySelector(element).addEventListener(
			"submit",
			async function (event) {
				before();
				event.preventDefault();
				const data = new FormData(event.target);
				const value = Object.fromEntries(data.entries());

				var url = validaUrl("/server/" + document.querySelector(element).action.replace(location.origin, "").replace("/", ""));
				var resp = {};

				if (document.querySelector(element).method == "post") {
					resp = JSON.parse(await Lis.post(url, value));
				}
				after(resp);
			},
			true
		);
	};

	//iniciando a pagina ===========================================
	init();
} catch (e) {
	console.log(e);
}
