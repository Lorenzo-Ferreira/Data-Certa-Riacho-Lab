# DataCerta — Plataforma de Combate ao Desperdício Alimentar

> Sistema web que conecta estabelecimentos com alimentos próximos do vencimento a consumidores interessados em comprá-los por preços reduzidos, incentivando o consumo consciente e reduzindo o desperdício alimentar.

## 🌐 Links do Projeto

- **Site oficial:** [datacerta.infinityfreeapp.com](https://datacerta.infinityfreeapp.com)

---

## 📌 Informações Gerais

- **Nome do Projeto:** DataCerta
- **Equipe:** Victor H., Henrique, Lorenzo, Arthur, Victor G., Miguel e Yuri
- **Curso:** Técnico em Informática
- **Instituição:** Fundação de Ensino de Contagem | FUNEC - Riacho
- **Ano/Semestre:** 2025 - 3º Trimestre
- **Professor Orientador:** Leonardo Pacheco
- **Cliente/Setor Solicitante:** Projeto acadêmico voltado à sustentabilidade e ao combate ao desperdício alimentar.

---

## 🎯 Objetivo do Projeto

Desenvolver um sistema web que permita a estabelecimentos comerciais anunciar alimentos próximos do vencimento com descontos, possibilitando que consumidores encontrem e adquiram esses produtos por preços mais acessíveis.

O DataCerta busca reduzir o desperdício de alimentos, incentivar o consumo consciente e proporcionar benefícios tanto aos consumidores quanto aos estabelecimentos participantes.

---

## 👥 Público-Alvo

O sistema será destinado a dois grupos principais:

- **Consumidores:** pessoas interessadas em comprar alimentos com desconto, economizando dinheiro e evitando o desperdício.
- **Estabelecimentos comerciais:** padarias, mercados, hortifrutis, mercearias e outros comércios que desejam vender alimentos próximos do vencimento.

---

## 🧩 Problema a Ser Resolvido

Diariamente, estabelecimentos comerciais descartam alimentos que ainda podem ser consumidos, mas que estão próximos do vencimento ou apresentam baixa procura devido à proximidade da data de validade.

Esse desperdício representa perdas financeiras para os comerciantes e contribui para problemas ambientais e sociais. Ao mesmo tempo, muitos consumidores procuram alternativas para economizar nas compras de alimentos.

A falta de uma plataforma que conecte esses dois públicos dificulta a comercialização desses produtos antes que percam sua validade.

---

## 💡 Solução Proposta

O DataCerta propõe uma plataforma web na qual estabelecimentos poderão cadastrar seus produtos próximos do vencimento, informando nome, descrição, imagem, preço original, preço promocional e data de validade.

Os consumidores poderão visualizar as ofertas disponíveis, pesquisar e filtrar produtos, consultar os descontos e realizar pedidos pelo sistema.

A plataforma deverá controlar a validade dos alimentos, impedindo que produtos vencidos continuem disponíveis para compra. Quando a data de validade for atingida, o produto deverá ser removido da lista de ofertas ou identificado como expirado.

Dessa forma, o DataCerta pretende transformar alimentos que poderiam ser descartados em oportunidades de venda com desconto.

---

## ⚙️ Funcionalidades do Sistema

### Funcionalidades para consumidores

- [ ] Cadastro e login de consumidores.
- [ ] Visualização das ofertas disponíveis.
- [ ] Pesquisa de produtos por nome.
- [ ] Filtros por categoria e outras características disponíveis.
- [ ] Visualização do preço original, preço promocional e percentual de desconto.
- [ ] Consulta à data de validade e às informações do estabelecimento.
- [ ] Adição e remoção de produtos dos favoritos.
- [ ] Adição de produtos ao carrinho.
- [ ] Finalização de pedidos.
- [ ] Consulta ao histórico e ao status dos pedidos.
- [ ] Avaliação dos produtos ou estabelecimentos.
- [ ] Gerenciamento dos dados do perfil.

### Funcionalidades para estabelecimentos

- [ ] Cadastro e login de estabelecimentos.
- [ ] Cadastro de informações comerciais, incluindo nome, e-mail, CNPJ, telefone, localização e formas de pagamento aceitas.
- [ ] Definição das categorias de alimentos comercializados.
- [ ] Gerenciamento do perfil do estabelecimento.
- [ ] Cadastro de produtos com nome, descrição, imagem, categoria e data de validade.
- [ ] Definição do preço original e do preço promocional.
- [ ] Edição e exclusão de produtos anunciados.
- [ ] Visualização e gerenciamento dos pedidos recebidos.
- [ ] Acompanhamento dos produtos anunciados.
- [ ] Remoção ou identificação automática de produtos cuja validade tenha expirado.

---

## 🛠️ Tecnologias Utilizadas

- **Front-end:** HTML5, CSS3 e JavaScript.
- **Back-end:** PHP.
- **Banco de Dados:** MySQL.
- **Gerenciamento do Banco de Dados:** phpMyAdmin.
- **Servidor de Desenvolvimento:** Apache, por meio do XAMPP.
- **Editor de Código:** Visual Studio Code.
- **Versionamento:** Git e GitHub.

O projeto utiliza tecnologias adequadas a um sistema web acadêmico, priorizando uma interface intuitiva, código de fácil manutenção e execução em ambiente local.

---

## 🗂️ Estrutura do Projeto

```text
datacerta/
│
├── css/
│   └── style.css
│
├── js/
│   └── main.js
│
├── partials/
│   └── hero_ilustracao.svg.php
│
├── uploads/
│
├── database/
│   └── database.sql
│
├── avaliacoes.php
├── avaliar.php
├── cadastro_estabelecimento.php
├── cadastro.php
├── carrinho_ajax.php
├── carrinho.php
├── conexao.php
├── configuracoes.php
├── criar_zip.php
├── dashboard.php
├── finalizar.php
├── footer.php
├── header.php
├── index.php
├── lgpd.php
├── login.php
├── logout.php
├── pedidos.php
├── perfil.php
├── produto_deletar.php
├── produto_detalhe.php
├── produto_form.php
├── produtos.php
└── teste.php
```

**Organização dos arquivos:**

- **`css/style.css`:** define a aparência, as cores, o layout e a responsividade das páginas.
- **`js/main.js`:** concentra os scripts JavaScript utilizados na interface.
- **`partials/`:** contém componentes reutilizáveis da interface.
- **`uploads/`:** armazena arquivos enviados ao sistema, como imagens de produtos.
- **`database/database.sql`:** contém o script SQL para criação e, quando previsto, inicialização do banco de dados.
- **`conexao.php`:** configura a conexão com o banco de dados MySQL.
- **Arquivos PHP:** implementam as páginas, os formulários e as funcionalidades do sistema.

*Observação: a estrutura apresentada representa a organização informada para o projeto. Os arquivos definitivos devem corresponder à versão efetivamente utilizada.*

---

## 🧪 Requisitos Funcionais

- **RF01** – O sistema deve permitir o cadastro e o login de consumidores.
- **RF02** – O sistema deve permitir o cadastro e o login de estabelecimentos.
- **RF03** – O sistema deve permitir a edição dos dados dos consumidores e estabelecimentos.
- **RF04** – O sistema deve permitir que estabelecimentos cadastrem produtos para venda.
- **RF05** – O cadastro de produtos deve incluir nome, categoria, descrição, imagem, data de validade, preço original e preço promocional.
- **RF06** – O sistema deve permitir a edição e a exclusão de produtos anunciados.
- **RF07** – O sistema deve calcular ou apresentar o percentual de desconto dos produtos.
- **RF08** – O sistema deve permitir que consumidores pesquisem e filtrem os produtos disponíveis.
- **RF09** – O sistema não deve disponibilizar para compra produtos cuja validade tenha expirado.
- **RF10** – O sistema deve identificar ou retirar automaticamente das ofertas os produtos que atingirem a data de validade.
- **RF11** – O sistema deve permitir que consumidores adicionem produtos aos favoritos e ao carrinho.
- **RF12** – O sistema deve permitir a criação e o acompanhamento de pedidos.
- **RF13** – O sistema deve permitir que estabelecimentos consultem e gerenciem os pedidos recebidos.
- **RF14** – O sistema deve permitir que consumidores consultem seus pedidos anteriores.
- **RF15** – O sistema deve permitir que consumidores avaliem produtos ou estabelecimentos, conforme as funcionalidades disponíveis.

---

## 🚫 Requisitos Não Funcionais

- **RNF01** – O sistema deve ser acessível por meio de um navegador web.
- **RNF02** – A interface deve ser intuitiva, organizada e responsiva.
- **RNF03** – O sistema deve apresentar informações dos produtos de maneira clara.
- **RNF04** – O banco de dados deve manter os registros organizados e consistentes.
- **RNF05** – O sistema deve funcionar em ambiente local utilizando XAMPP, Apache e MySQL.
- **RNF06** – As senhas dos usuários devem ser armazenadas de forma segura, utilizando mecanismos apropriados de hash.

---

## 📅 Cronograma do Projeto

| Mês | Tarefa | Responsável(is) | Prazo |
|---|---|---|---|
| Abril | Design das telas do site | Victor H. e Henrique | 5 a 25 |
| Maio | Começar a codificar o protótipo | Lorenzo, Arthur e Victor H. | 4 a 30 |
| Junho | Desenvolvimento do site com integrações | Victor G., Lorenzo e Arthur | 1 a 26 |
| Julho | Desenvolvimento do site com integrações | Victor G., Lorenzo e Arthur | 1 a 14 |
| Agosto | Desenvolvimento do site com integrações | Victor G., Lorenzo e Arthur | 3 a 28 |
| Setembro | Versões finais e Instagram | Victor H., Lorenzo e Arthur | 1 a 25 |
| Outubro | Testes finais e ajustes necessários | Miguel e Yuri | 5 a 23 |

---

## 🧑‍💻 Integrantes da Equipe

| Nome | Função/Área de atuação | Responsabilidade no projeto |
|---|---|---|
| Victor H. | Design e desenvolvimento | Participação no design das telas, codificação do protótipo e preparação das versões finais e do Instagram. |
| Henrique | Design | Participação no design das telas do site. |
| Lorenzo | Desenvolvimento | Participação na codificação do protótipo, no desenvolvimento com integrações e nas versões finais. |
| Arthur | Desenvolvimento | Participação na codificação do protótipo, no desenvolvimento com integrações e nas versões finais. |
| Victor G. | Desenvolvimento | Participação no desenvolvimento do site com integrações. |
| Miguel | Testes | Participação nos testes finais e nos ajustes necessários. |
| Yuri | Testes | Participação nos testes finais e nos ajustes necessários. |

*Observação: as áreas de atuação foram inferidas a partir das tarefas atribuídas no cronograma e podem ser ajustadas caso a equipe tenha definido funções diferentes.*

---

## 🔐 Controle de Acesso

O DataCerta contará com dois perfis de usuário:

- **Consumidor:** poderá consultar ofertas, pesquisar produtos, gerenciar favoritos, utilizar o carrinho, realizar pedidos, consultar seu histórico e acessar seu perfil.
- **Estabelecimento:** poderá gerenciar seus dados comerciais, cadastrar e editar produtos, controlar os anúncios e acompanhar os pedidos recebidos.

Cada perfil terá acesso às funcionalidades correspondentes à sua finalidade no sistema.

---

## 📷 Protótipos / Telas

O desenvolvimento da interface utiliza como referência o protótipo `data_certa.html` e o guia visual do projeto, que reúne as orientações de identidade visual e Design System do DataCerta.

- **Guia Visual — Design System DataCerta:** [Acessar o guia visual no Notion](https://app.notion.com/p/Guia-Visual-Desing-System-Data-Certa-376acc3d11f08004a76fe45c7b7edb43)

As principais telas do sistema incluem:

- Página inicial de apresentação do DataCerta.
- Cadastro e login de consumidores.
- Cadastro e login de estabelecimentos.
- Página de ofertas e pesquisa de produtos.
- Página de detalhes do produto.
- Carrinho e finalização de pedidos.
- Perfil do consumidor.
- Painel de gerenciamento do estabelecimento.
- Cadastro e edição de produtos.
- Consulta e gerenciamento de pedidos.
- Avaliação de produtos ou estabelecimentos.

---

## 📦 Como Executar o Projeto

### Pré-requisitos

- XAMPP instalado.
- Apache e MySQL disponíveis no XAMPP.
- Navegador web.
- Editor de código, como o Visual Studio Code.

### Passo a passo

1. Baixe ou clone o repositório do DataCerta.
2. Coloque a pasta `datacerta` no diretório `htdocs` do XAMPP, geralmente localizado em:

   ```text
   C:\xampp\htdocs\
   ```

3. Abra o painel de controle do XAMPP e inicie os serviços **Apache** e **MySQL**.
4. Acesse o phpMyAdmin pelo navegador:

   ```text
   http://localhost/phpmyadmin
   ```

5. No phpMyAdmin, selecione a opção **Importar** e escolha o arquivo:

   ```text
   C:\xampp\htdocs\datacerta\database\database.sql
   ```

6. Execute a importação para criar as tabelas e os dados iniciais definidos no script SQL. Caso o banco de dados ainda não exista, verifique se o script o cria automaticamente. Se não criar, será necessário criar o banco manualmente antes da importação.
7. Abra o arquivo `conexao.php` e confira as configurações de conexão com o MySQL, ajustando o nome do banco, o usuário e a senha conforme o ambiente local.
8. Acesse o sistema pelo navegador:

   ```text
   http://localhost/datacerta/
   ```

9. Teste as funcionalidades implementadas, incluindo cadastro, login, gerenciamento de produtos, carrinho, pedidos e controle de validade.

### Observações

- O nome do banco de dados configurado em `conexao.php` deve corresponder ao banco criado ou utilizado pelo script SQL.
- O Apache e o MySQL devem permanecer ativos enquanto o sistema estiver sendo executado localmente.

---

## 🌱 Impacto Esperado

O DataCerta pretende contribuir para:

- Reduzir o descarte desnecessário de alimentos próprios para consumo.
- Oferecer produtos com preços mais acessíveis aos consumidores.
- Diminuir as perdas financeiras dos estabelecimentos comerciais.
- Incentivar hábitos de consumo mais conscientes.
- Promover a sustentabilidade por meio da tecnologia.

---

## 📄 Licença

Projeto desenvolvido para fins acadêmicos no curso Técnico em Informática.
