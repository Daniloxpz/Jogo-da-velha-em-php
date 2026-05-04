<?php
/* 
Curso: Sistemas de Informação
Disciplina: Programação para Computadores 1
Professor: Filipe Costa
Aluno: Danilo Monteiro
*/

/* 
Programa Jogo da Velha em PHP
Instruções: Complete as funções onde consta "Em construção".
Não altere a estrutura lógica já existente. 
*/

// Inicia a sessão para guardar os dados do jogo (tabuleiro e status) entre as jogadas
session_start(); 

// Constantes
// Define o tamanho do tabuleiro (3x3)
define('LIM', 3); 
// Define a letra que representa o jogador
define('JOGADOR', 'J'); 
// Define a letra que representa a máquina
define('MAQUINA', 'M'); 

// Inicialização do Jogo
// Verifica se o jogo (sessão 'jv') ainda não foi criado
if (!isset($_SESSION['jv'])) { 
    // Se não foi criado, chama a função para criar e limpar o tabuleiro
    limpeza(); 
}

// Lógica de controle de rotas (Ações do usuário)
// Verifica se alguma ação foi passada na URL (via GET)
if (isset($_GET['acao'])) { 
    // Se a ação for 'reiniciar'
    if ($_GET['acao'] == 'reiniciar') { 
        // Chama a função de limpeza para zerar o jogo
        limpeza(); 
    // Se a ação for 'jogar' e a linha (l) e coluna (c) foram enviadas
    } elseif ($_GET['acao'] == 'jogar' && isset($_GET['l']) && isset($_GET['c'])) { 
        // Converte linha e coluna para inteiros e executa a jogada do usuário
        jogadorJoga((int)$_GET['l'], (int)$_GET['c']); 
    }
}

// --- PROCEDIMENTOS E FUNÇÕES ---

/**
 * Procedimento utilizado para limpeza da matriz, colocando '0' em todas as posições.
 * Deve também reiniciar as variáveis de sessão: 'vitoria', 'empate' e 'turno'.
 */
function limpeza() {
    // Cria uma matriz 3x3 preenchida com o caractere '0' para representar espaços vazios
    $_SESSION['jv'] = array_fill(0, LIM, array_fill(0, LIM, '0'));
    // Zera a variável de vitória (0 = ninguém, 1 = Jogador, 2 = Máquina)
    $_SESSION['vitoria'] = 0; 
    // Zera a variável de empate (0 = não, 1 = sim)
    $_SESSION['empate'] = 0;  
    // Zera o contador de turnos/jogadas
    $_SESSION['turno'] = 0;   
}

/**
 * Função que verifica se houve ganhador.
 * @param char $jog (J ou M)
 * @return int (1 para vitória, 0 para continuar)
 */
function velha($jog) {
    // Cria um laço de repetição para verificar as 3 linhas e as 3 colunas
    for ($i = 0; $i < LIM; $i++) {
        // Verifica se todas as 3 posições da linha $i têm o símbolo do jogador atual ($jog)
        if ($_SESSION['jv'][$i][0] == $jog && $_SESSION['jv'][$i][1] == $jog && $_SESSION['jv'][$i][2] == $jog) {
            return 1; // Se sim, retorna 1 (vitória)
        }
        // Verifica se todas as 3 posições da coluna $i têm o símbolo do jogador atual ($jog)
        if ($_SESSION['jv'][0][$i] == $jog && $_SESSION['jv'][1][$i] == $jog && $_SESSION['jv'][2][$i] == $jog) {
            return 1; // Se sim, retorna 1 (vitória)
        }
    }
    
    // Verifica a diagonal principal (posições 0,0 ; 1,1 ; 2,2)
    if ($_SESSION['jv'][0][0] == $jog && $_SESSION['jv'][1][1] == $jog && $_SESSION['jv'][2][2] == $jog) {
        return 1; // Se sim, retorna 1 (vitória)
    }
    
    // Verifica a diagonal secundária (posições 0,2 ; 1,1 ; 2,0)
    if ($_SESSION['jv'][0][2] == $jog && $_SESSION['jv'][1][1] == $jog && $_SESSION['jv'][2][0] == $jog) {
        return 1; // Se sim, retorna 1 (vitória)
    }
    
    // Se não encontrou nenhuma sequência de 3, retorna 0 (o jogo continua)
    return 0; 
}

/**
 * Verifica se a posição da matriz já foi selecionada.
 * @return int (1 se ocupada, 0 se livre)
 */
function posicao($l, $c) {
    // Se o valor na posição for diferente de '0', significa que já foi jogada
    if ($_SESSION['jv'][$l][$c] != '0') {
        return 1; // Retorna 1 indicando que está ocupada
    }
    // Caso contrário, retorna 0 indicando que está livre
    return 0; 
} 

/**
 * Executa a jogada do usuário com base nos cliques na tabela.
 */
function jogadorJoga($l, $col) { 
    // Se o jogo já acabou (alguém venceu), impede novas jogadas retornando vazio
    if ($_SESSION['vitoria'] != 0) return;      
    
    // Verifica se a posição clicada está livre (retorno 0)
    if (posicao($l, $col) == 0) {         
        // Marca a posição com o símbolo do JOGADOR ('J')
        $_SESSION['jv'][$l][$col] = JOGADOR;         
        
        // Verifica se o JOGADOR venceu após essa jogada
        if (velha(JOGADOR)) {             
            // Se venceu, atualiza o status de vitória para 1
            $_SESSION['vitoria'] = 1;         
        } else {             
            // Se não venceu, passa a vez para a máquina jogar
            maquinaJoga();         
        }     
    }
} 

/**
 * Lógica da jogada da máquina (aleatória).
 */
function maquinaJoga() {     
    // Cria uma variável para controlar se ainda existem espaços livres no tabuleiro
    $espacosLivres = false;
    
    // Percorre toda a matriz para procurar espaços com '0'
    for ($i = 0; $i < LIM; $i++) {
        for ($j = 0; $j < LIM; $j++) {
            if ($_SESSION['jv'][$i][$j] == '0') {
                $espacosLivres = true; // Encontrou pelo menos um espaço livre
            }
        }
    }

    // Se não houver mais espaços livres, sai da função para evitar um loop infinito (deu velha)
    if ($espacosLivres == false) {
        $_SESSION['empate'] = 1;
        return; 
    }

    // Inicia um laço que vai tentar adivinhar posições aleatórias
    do {
        // Sorteia um número de 0 a 2 para a linha
        $l = rand(0, 2); 
        // Sorteia um número de 0 a 2 para a coluna
        $c = rand(0, 2); 
    // O laço repete ENQUANTO a posição sorteada estiver ocupada (retorno 1)
    } while (posicao($l, $c) == 1); 

    // Assim que achar uma posição livre, marca com o símbolo da MÁQUINA ('M')
    $_SESSION['jv'][$l][$c] = MAQUINA;

    // Verifica se a MÁQUINA venceu após essa jogada aleatória
    if (velha(MAQUINA)) {
        // Se venceu, atualiza o status de vitória para 2
        $_SESSION['vitoria'] = 2; 
    }
} 
?> 

<!DOCTYPE html>
<html lang="pt-br">
<head>     
    <meta charset="UTF-8">     
    <title>Jogo da Velha - Programação I</title>     
    <style>         
        table { border-collapse: collapse; margin: 20px auto; }         
        td { width: 60px; height: 60px; border: 2px solid #333; text-align: center; font-size: 24px; font-family: sans-serif; }         
        a { text-decoration: none; display: block; line-height: 60px; color: #007bff; }         
        .msg { text-align: center; font-weight: bold; }     
    </style>
</head>
<body>      
    <h2 style="text-align:center;">Jogo da Velha - Prof. Filipe Costa</h2>      
    <div class="msg">         
        <?php              
            // Exibe a mensagem de acordo com a variável de sessão 'vitoria'
            if ($_SESSION['vitoria'] == 1) echo "VOCÊ VENCEU!";             
            elseif ($_SESSION['vitoria'] == 2) echo "A MÁQUINA VENCEU!";             
            // Adicionei uma checagem rápida para caso dê velha (empate) e o jogo acabe
            elseif ($_SESSION['empate'] == 1) echo "DEU VELHA! EMPATE!";
            else echo "Sua vez de jogar!";         
        ?>     
    </div>      
    <table>         
        <!-- Loop para criar as 3 linhas da tabela (HTML) -->
        <?php for ($i = 0; $i < LIM; $i++): ?>             
            <tr>                 
                <!-- Loop para criar as 3 colunas (células) em cada linha -->
                <?php for ($j = 0; $j < LIM; $j++): ?>                     
                    <td>                         
                        <!-- Se a posição estiver livre ('0') e ninguém venceu ainda -->
                        <?php if ($_SESSION['jv'][$i][$j] == '0' && $_SESSION['vitoria'] == 0): ?>                             
                            <!-- Cria um link clicável que envia a linha ($i) e coluna ($j) via URL -->
                            <a href="?acao=jogar&l=<?php echo $i; ?>&c=<?php echo $j; ?>">?</a>                         
                        <?php else: ?>                             
                            <!-- Se já estiver ocupada, apenas exibe o símbolo ('J' ou 'M'). Se for '0', exibe vazio. -->
                            <?php echo ($_SESSION['jv'][$i][$j] == '0') ? "" : $_SESSION['jv'][$i][$j]; ?>                         
                        <?php endif; ?>                     
                    </td>                 
                <?php endfor; ?>             
            </tr>         
        <?php endfor; ?>     
    </table>      
    <div style="text-align:center;">         
        <!-- Botão para reiniciar a partida, enviando a ação 'reiniciar' via URL -->
        <a href="?acao=reiniciar">Reiniciar Jogo</a>     
    </div>  
<!-- Code injected by live-server -->
<script>
	// <![CDATA[  <-- For SVG support
	if ('WebSocket' in window) {
		(function () {
			function refreshCSS() {
				var sheets = [].slice.call(document.getElementsByTagName("link"));
				var head = document.getElementsByTagName("head")[0];
				for (var i = 0; i < sheets.length; ++i) {
					var elem = sheets[i];
					var parent = elem.parentElement || head;
					parent.removeChild(elem);
					var rel = elem.rel;
					if (elem.href && typeof rel != "string" || rel.length == 0 || rel.toLowerCase() == "stylesheet") {
						var url = elem.href.replace(/(&|\?)_cacheOverride=\d+/, '');
						elem.href = url + (url.indexOf('?') >= 0 ? '&' : '?') + '_cacheOverride=' + (new Date().valueOf());
					}
					parent.appendChild(elem);
				}
			}
			var protocol = window.location.protocol === 'http:' ? 'ws://' : 'wss://';
			var address = protocol + window.location.host + window.location.pathname + '/ws';
			var socket = new WebSocket(address);
			socket.onmessage = function (msg) {
				if (msg.data == 'reload') window.location.reload();
				else if (msg.data == 'refreshcss') refreshCSS();
			};
			if (sessionStorage && !sessionStorage.getItem('IsThisFirstTime_Log_From_LiveServer')) {
				console.log('Live reload enabled.');
				sessionStorage.setItem('IsThisFirstTime_Log_From_LiveServer', true);
			}
		})();
	}
	else {
		console.error('Upgrade your browser. This Browser is NOT supported WebSocket for Live-Reloading.');
	}
	// ]]>
</script>
</body>
</html>