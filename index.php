<?php
session_start();

/* Mercado Fresco: loja demonstrativa em PHP, sem banco de dados.
   O carrinho e os favoritos ficam salvos na sessão do navegador. */

$produtos = [
 ['id'=>1,'nome'=>'Milho Verde','categoria'=>'Legumes','preco'=>12.90,'peso'=>'300 g','emoji'=>'🌽','desc'=>'Ideal para tortas, saladas e acompanhamentos.'],
 ['id'=>2,'nome'=>'Ervilha Congelada','categoria'=>'Legumes','preco'=>10.50,'peso'=>'300 g','emoji'=>'🟢','desc'=>'Prática para deixar suas receitas mais coloridas.'],
 ['id'=>3,'nome'=>'Couve-flor','categoria'=>'Vegetais','preco'=>13.90,'peso'=>'300 g','emoji'=>'🥦','desc'=>'Selecionada e pronta para preparar.'],
 ['id'=>4,'nome'=>'Brócolis','categoria'=>'Vegetais','preco'=>14.90,'peso'=>'300 g','emoji'=>'🥦','desc'=>'Um acompanhamento fácil para o dia a dia.'],
 ['id'=>5,'nome'=>'Batata Palito','categoria'=>'Batatas','preco'=>16.90,'peso'=>'400 g','emoji'=>'🍟','desc'=>'Prepare no forno ou na air fryer.'],
 ['id'=>6,'nome'=>'Mix de Legumes','categoria'=>'Legumes','preco'=>12.50,'peso'=>'300 g','emoji'=>'🥕','desc'=>'Uma mistura prática e cheia de cores.'],
 ['id'=>7,'nome'=>'Batata Rústica','categoria'=>'Batatas','preco'=>18.90,'peso'=>'400 g','emoji'=>'🥔','desc'=>'Perfeita para acompanhar suas refeições.'],
 ['id'=>8,'nome'=>'Anéis de Cebola','categoria'=>'Petiscos','preco'=>17.90,'peso'=>'300 g','emoji'=>'🧅','desc'=>'Um petisco simples para compartilhar.'],
 ['id'=>9,'nome'=>'Cogumelos Fatiados','categoria'=>'Vegetais','preco'=>15.90,'peso'=>'200 g','emoji'=>'🍄','desc'=>'Para massas, risotos e outras receitas.'],
 ['id'=>10,'nome'=>'Frutas Vermelhas','categoria'=>'Frutas','preco'=>22.90,'peso'=>'300 g','emoji'=>'🍒','desc'=>'Ótimas para vitaminas e sobremesas.'],
 ['id'=>11,'nome'=>'Amora Congelada','categoria'=>'Frutas','preco'=>19.90,'peso'=>'300 g','emoji'=>'🫐','desc'=>'Saborosa em sucos e smoothies.'],
 ['id'=>12,'nome'=>'Morango Congelado','categoria'=>'Frutas','preco'=>18.90,'peso'=>'300 g','emoji'=>'🍓','desc'=>'Prático para bebidas e receitas doces.'],
 ['id'=>13,'nome'=>'Espinafre','categoria'=>'Vegetais','preco'=>11.90,'peso'=>'300 g','emoji'=>'🥬','desc'=>'Uma opção rápida para refogados.'],
 ['id'=>14,'nome'=>'Cenoura em Cubos','categoria'=>'Legumes','preco'=>9.90,'peso'=>'300 g','emoji'=>'🥕','desc'=>'Facilita o preparo de sopas e pratos.'],
 ['id'=>15,'nome'=>'Mix de Frutas','categoria'=>'Frutas','preco'=>21.90,'peso'=>'300 g','emoji'=>'🍇','desc'=>'Uma combinação refrescante para o lanche.'],
 ['id'=>16,'nome'=>'Milho para receitas','categoria'=>'Legumes','preco'=>11.50,'peso'=>'300 g','emoji'=>'🌽','desc'=>'Versátil para várias receitas caseiras.']
];

if (!isset($_SESSION['carrinho'])) $_SESSION['carrinho'] = [];
if (!isset($_SESSION['favoritos'])) $_SESSION['favoritos'] = [];
function h($v) { return htmlspecialchars((string)$v, ENT_QUOTES, 'UTF-8'); }
function moeda($v) { return 'R$ ' . number_format((float)$v, 2, ',', '.'); }

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $acao = $_POST['acao'] ?? '';
    $id = (int)($_POST['id'] ?? 0);
    if ($acao === 'adicionar' && $id > 0) {
        $_SESSION['carrinho'][$id] = min(99, ($_SESSION['carrinho'][$id] ?? 0) + 1);
        $_SESSION['mensagem'] = 'Produto adicionado ao carrinho!';
    } elseif ($acao === 'quantidade' && $id > 0) {
        $q = max(0, min(99, (int)($_POST['quantidade'] ?? 1)));
        if ($q === 0) unset($_SESSION['carrinho'][$id]); else $_SESSION['carrinho'][$id] = $q;
    } elseif ($acao === 'remover' && $id > 0) {
        unset($_SESSION['carrinho'][$id]);
        $_SESSION['mensagem'] = 'Produto removido do carrinho.';
    } elseif ($acao === 'limpar') {
        $_SESSION['carrinho'] = [];
        $_SESSION['mensagem'] = 'Carrinho esvaziado.';
    } elseif ($acao === 'favoritar' && $id > 0) {
        if (in_array($id, $_SESSION['favoritos'])) {
            $_SESSION['favoritos'] = array_values(array_diff($_SESSION['favoritos'], [$id]));
        } else {
            $_SESSION['favoritos'][] = $id;
        }
    }
    $destino = strtok($_SERVER['REQUEST_URI'], '?');
    $query = $_GET ? '?' . http_build_query($_GET) : '';
    header('Location: ' . $destino . $query);
    exit;
}

$categoria = $_GET['categoria'] ?? 'Todos';
$busca = trim($_GET['busca'] ?? '');
$ordem = $_GET['ordem'] ?? 'destaques';
$favoritosOnly = isset($_GET['favoritos']);
$categorias = ['Todos','Frutas','Legumes','Vegetais','Batatas','Petiscos'];
$lista = array_values(array_filter($produtos, function($p) use ($categoria, $busca, $favoritosOnly) {
    $catOk = $categoria === 'Todos' || $p['categoria'] === $categoria;
    $buscaOk = $busca === '' || stripos($p['nome'].' '.$p['categoria'].' '.$p['desc'], $busca) !== false;
    $favOk = !$favoritosOnly || in_array($p['id'], $_SESSION['favoritos']);
    return $catOk && $buscaOk && $favOk;
}));
if ($ordem === 'menor') usort($lista, fn($a,$b) => $a['preco'] <=> $b['preco']);
elseif ($ordem === 'maior') usort($lista, fn($a,$b) => $b['preco'] <=> $a['preco']);
elseif ($ordem === 'nome') usort($lista, fn($a,$b) => strcmp($a['nome'],$b['nome']));

$porId = [];
foreach ($produtos as $p) $porId[$p['id']] = $p;
$totalItens = array_sum($_SESSION['carrinho']);
$total = 0;
foreach ($_SESSION['carrinho'] as $id=>$qtd) if (isset($porId[$id])) $total += $porId[$id]['preco'] * $qtd;
$mensagem = $_SESSION['mensagem'] ?? '';
unset($_SESSION['mensagem']);
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Mercado Fresco | Frutas e vegetais</title>
<link rel="stylesheet" href="style.css">
</head>
<body>
<div class="top-strip"><div>Produtos selecionados para facilitar sua rotina <span>Entrega prática • Qualidade • Sabor</span></div></div>
<header class="header">
  <a class="brand" href="index.php"><span class="brand-icon">🥦</span><span><b>Mercado Fresco</b><small>Mais praticidade para uma vida saudável</small></span></a>
  <form class="search" method="get" action="index.php">
    <?php if ($categoria !== 'Todos'): ?><input type="hidden" name="categoria" value="<?=h($categoria)?>"><?php endif; ?>
    <input name="busca" type="search" placeholder="Busque frutas, legumes e vegetais..." value="<?=h($busca)?>">
    <button aria-label="Buscar">⌕</button>
  </form>
  <button class="cart-trigger" id="openCart" type="button">🛒 Carrinho <b><?= (int)$totalItens ?></b></button>
</header>
<nav class="nav"><a href="#produtos">Comprar</a><a href="#beneficios">Por que escolher?</a><a href="#receitas">Receitas</a><a href="#rodape">Contato</a></nav>

<?php if ($mensagem): ?><div class="flash"><?=h($mensagem)?></div><?php endif; ?>

<main>
<section class="hero">
  <div class="hero-copy"><p class="eyebrow">FRESCOR QUE FACILITA</p><h1>Comer bem ficou <em>mais fácil.</em></h1><p>Frutas e vegetais selecionados para deixar suas refeições mais práticas, gostosas e coloridas.</p><a class="button" href="#produtos">Ver produtos <span>→</span></a><div class="hero-note">✓ Praticidade no seu dia a dia &nbsp; ✓ Mais sabor nas refeições</div></div>
  <div class="produce-art"><div class="produce-circle">🥦<span>🥕</span><span>🍓</span><span>🌽</span><span>🫐</span></div><div class="fresh-stamp">FRESCO<br>TODO DIA</div></div>
</section>

<section class="shop" id="produtos">
  <div class="section-title"><div><p class="eyebrow">ESCOLHA SEUS FAVORITOS</p><h2>Frutas e vegetais congelados</h2><p class="sub">Praticidade para ter sempre à mão.</p></div><a class="outline" href="?favoritos=1#produtos">♡ Favoritos (<?=count($_SESSION['favoritos'])?>)</a></div>
  <div class="categories">
  <?php foreach ($categorias as $cat): ?>
    <a class="category <?=($categoria===$cat && !$favoritosOnly)?'selected':''?>" href="?<?=h(http_build_query(array_filter(['categoria'=>$cat,'busca'=>$busca,'ordem'=>$ordem])))?>"><span><?=['Todos'=>'🧺','Frutas'=>'🍓','Legumes'=>'🥕','Vegetais'=>'🥬','Batatas'=>'🍟','Petiscos'=>'🧅'][$cat]?></span><small><?=h($cat)?></small></a>
  <?php endforeach; ?>
  </div>
  <div class="filter-line"><span><?=count($lista)?> produto(s) encontrado(s)<?=$favoritosOnly?' nos favoritos':''?></span>
    <form method="get"><input type="hidden" name="categoria" value="<?=h($categoria)?>"><input type="hidden" name="busca" value="<?=h($busca)?>"><label for="ordem">Ordenar:</label><select name="ordem" id="ordem" onchange="this.form.submit()"><option value="destaques" <?=$ordem==='destaques'?'selected':''?>>Destaques</option><option value="menor" <?=$ordem==='menor'?'selected':''?>>Menor preço</option><option value="maior" <?=$ordem==='maior'?'selected':''?>>Maior preço</option><option value="nome" <?=$ordem==='nome'?'selected':''?>>Nome</option></select></form>
  </div>
  <div class="products">
  <?php if (!$lista): ?><div class="empty">Nenhum produto encontrado. <a href="index.php#produtos">Ver todos os produtos</a></div><?php endif; ?>
  <?php foreach ($lista as $p): $fav = in_array($p['id'], $_SESSION['favoritos']); ?>
    <article class="product-card">
      <div class="product-photo"><span class="product-emoji"><?=$p['emoji']?></span><?php if ($p['id']===1 || $p['id']===4): ?><span class="tag">Popular</span><?php endif; ?>
        <form method="post" class="favorite-form"><input type="hidden" name="acao" value="favoritar"><input type="hidden" name="id" value="<?=$p['id']?>"><button class="heart <?=$fav?'liked':''?>" title="<?=$fav?'Remover dos favoritos':'Adicionar aos favoritos'?>" aria-label="Favoritar"><?=$fav?'♥':'♡'?></button></form>
      </div>
      <div class="product-info"><small class="product-category"><?=h($p['categoria'])?> • <?=h($p['peso'])?></small><h3><?=h($p['nome'])?></h3><p><?=h($p['desc'])?></p><div class="rating">★★★★★ <span>(produto selecionado)</span></div><div class="price"><?=moeda($p['preco'])?></div><form method="post"><input type="hidden" name="acao" value="adicionar"><input type="hidden" name="id" value="<?=$p['id']?>"><button class="add-button">＋ Adicionar ao carrinho</button></form></div>
    </article>
  <?php endforeach; ?>
  </div>
</section>

<section class="editorial" id="beneficios">
  <div class="editorial-copy"><p class="eyebrow">MAIS TEMPO PARA VOCÊ</p><h2>Frutas e vegetais para quem não abre mão de ser saudável</h2><p>Tenha ingredientes práticos sempre à mão. Eles ajudam a simplificar o preparo das refeições e a variar o cardápio durante a semana.</p><p>Escolha seus favoritos e monte uma compra que combina com sua rotina.</p></div><div class="editorial-art fruit-bowl">🍓<span>🫐</span><span>🍇</span><span>🍊</span></div>
</section>
<section class="editorial reverse">
  <div class="editorial-copy"><p class="eyebrow">PRATICIDADE TODOS OS DIAS</p><h2>Legumes congelados para facilitar sua rotina</h2><p>Menos tempo preparando ingredientes e mais tempo aproveitando suas receitas favoritas.</p><h3>A sua cozinha, do seu jeito.</h3><p>Explore combinações, experimente novas receitas e aproveite cada refeição.</p><a href="#receitas" class="text-link">Descobrir receitas →</a></div><div class="editorial-art veggie-bowl">🥗<span>🥕</span><span>🥦</span></div>
</section>
<section class="benefit-band"><h2>Pequenas escolhas, grandes facilidades</h2><div class="benefit-grid"><div><b>🌱 Ingredientes selecionados</b><p>Opções variadas para o seu cardápio.</p></div><div><b>⏱️ Mais praticidade</b><p>Facilite o preparo das refeições.</p></div><div><b>🧺 Compra simples</b><p>Encontre seus produtos em um só lugar.</p></div><div><b>💚 Mais variedade</b><p>Experimente novas combinações.</p></div></div></section>
<section class="recipes" id="receitas"><div class="section-title"><div><p class="eyebrow">INSPIRAÇÃO PARA A COZINHA</p><h2>Ideias gostosas para hoje</h2><p class="sub">Use seus ingredientes favoritos em receitas simples.</p></div></div><div class="recipe-grid"><article><div>🥦🥕</div><h3>Legumes salteados</h3><p>Uma opção colorida para acompanhar almoço ou jantar.</p></article><article><div>🍅🥗</div><h3>Salada da estação</h3><p>Combine folhas, vegetais e um molho de sua preferência.</p></article><article><div>🍓🫐</div><h3>Smoothie de frutas</h3><p>Bata frutas com leite ou bebida vegetal para um lanche rápido.</p></article></div></section>
</main>
<footer id="rodape"><div class="footer-brand">🥦 <b>Mercado Fresco</b><p>Praticidade e sabor para sua rotina.</p></div><div><b>Navegue</b><a href="#produtos">Produtos</a><a href="#beneficios">Sobre nós</a><a href="#receitas">Receitas</a></div><div><b>Atendimento</b><p>Segunda a sexta, das 8h às 18h</p><p>contato@mercadofresco.exemplo</p></div><div class="copyright">© <?=date('Y')?> Mercado Fresco. Projeto demonstrativo para estudo.</div></footer>

<div class="drawer-backdrop" id="cartBackdrop"></div>
<aside class="cart-drawer" id="cartDrawer" aria-hidden="true">
  <div class="drawer-head"><div><p class="eyebrow">SUA COMPRA</p><h2>Meu carrinho</h2></div><button id="closeCart" aria-label="Fechar carrinho">×</button></div>
  <?php if (!$totalItens): ?><div class="cart-empty"><span>🧺</span><h3>Seu carrinho está vazio</h3><p>Adicione produtos para começar sua compra.</p><button id="continueShopping" class="button">Continuar comprando</button></div>
  <?php else: ?><div class="cart-items">
    <?php foreach ($_SESSION['carrinho'] as $id=>$qtd): if (!isset($porId[$id])) continue; $p=$porId[$id]; ?>
      <div class="cart-item"><div class="cart-item-emoji"><?=$p['emoji']?></div><div class="cart-item-details"><b><?=h($p['nome'])?></b><small><?=moeda($p['preco'])?> / pacote</small><form method="post" class="quantity-form"><input type="hidden" name="acao" value="quantidade"><input type="hidden" name="id" value="<?=$id?>"><button type="button" class="qty-minus" aria-label="Diminuir">−</button><input name="quantidade" value="<?=$qtd?>" type="number" min="0" max="99" aria-label="Quantidade"><button type="button" class="qty-plus" aria-label="Aumentar">+</button><button type="submit" class="update-qty">Atualizar</button></form><form method="post"><input type="hidden" name="acao" value="remover"><input type="hidden" name="id" value="<?=$id?>"><button class="remove-item">Remover</button></form></div><b class="item-subtotal"><?=moeda($p['preco']*$qtd)?></b></div>
    <?php endforeach; ?>
  </div><div class="cart-summary"><div><span>Itens</span><b><?=$totalItens?></b></div><div class="total-row"><span>Total estimado</span><strong><?=moeda($total)?></strong></div><p class="cart-disclaimer">Loja demonstrativa: nenhum pagamento será realizado.</p><button class="checkout" id="checkout">Finalizar pedido</button><form method="post"><input type="hidden" name="acao" value="limpar"><button class="clear-cart">Esvaziar carrinho</button></form></div><?php endif; ?>
</aside>
<div class="toast" id="toast">Pedido demonstrativo pronto! Nenhum pagamento foi realizado.</div>
<script src="script.js"></script>
</body>
</html>
