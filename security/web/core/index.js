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
	];

	const stylesGlobais = [
		"/framework/bootstrap-5.1.3-dist/css/bootstrap.css", //bootstrap version 5.1.3
		"/css/global/variaveisBootstrap.css", //arquivo para substituir variaveis do bootstrap
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

		//cria o elemento de carregando
		Lis.createComponent("carregando", "html", "append", ["/components/css/carregando.css"], ["/components/js/carregando.js"]);

		incluiScript(["/css/global/variaveis.css", "/coreCss"], "css"); //inclui o core css da aplicação

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

		document.querySelector("html").onerror = function (erro) {
			window.location.href = VarsGlobal["url"]+"/error";
		};
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
	 * @param {obj} header - cabeçalho da requisição
	 * @return resposta do post
	 */
	Lis.post = async function (url, dados, headers = {}) {
		const data = await fetch(url, {
			body: dados,
			method: "POST",
			headers: headers,
			data: dados,
		});

		var texto = await data.text();

		return texto;
	};

	/**
	 *
	 * @param {string} component Nome do component a ser colocado
	 * @param {string} element local onde o elemento será criado
	 * @param {string} local - como será criado o elemento (append, prepend)
	 * @param {array} js - array de strings com os nomes dos js dos componentes
	 * @param {array} css - array de strings com os nomes dos css dos componentes
	 *
	 * @return {void} - Função não tem retorno
	 */
	Lis.createComponent = function (component, element, local, css = [], js = []) {
		if(component == "carregando"){ //elemento carregando recebe classes e ids para que de o efeito certo
			var elemento = document.createElement(component);
		}else{
			var elemento = document.createElement("section");
			elemento.setAttribute("class", "component-"+component);
		}
		switch(local){
			case "append":
				document.querySelector(element).append(elemento);
				break;
			case "prepend":
			default:
				document.querySelector(element).prepend(elemento);
		}

		elemento.innerHTML = Lis.get(validaUrl("/components/html/" + component + ".html"), false);
		incluiScript(js, "js");
		incluiScript(css, "css");
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
				event.preventDefault();
				if(await before() != false){
					const data = new FormData(event.target);
					var url = validaUrl("/server/" + document.querySelector(element).action.replace(location.origin, "").replace("/", ""));
					var resp = {};
					if (document.querySelector(element).method == "post") {
						resp = JSON.parse(await Lis.post(url, data));
					}
					after(resp);
				}
			},
			true
		);
	};

	//iniciando a pagina ===========================================
	init();
} catch (e) {
	console.log(e);
}
