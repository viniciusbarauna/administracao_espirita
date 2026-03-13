<div align="center">
  <img src="https://via.placeholder.com/1000x300/0d6efd/ffffff?text=Sistema+de+Gestão+-+Lar+Espírita" alt="Banner do Projeto" />

  <br><br>

  # 🕊️ Sistema de Gestão para Lar Espírita

  **Uma aplicação web desenvolvida para modernizar, organizar e automatizar a gestão administrativa, financeira e de comunicação de centros espíritas e casas de caridade.**

  <p>
    <img src="https://img.shields.io/badge/PHP-777BB4?style=for-the-badge&logo=php&logoColor=white" alt="PHP" />
    <img src="https://img.shields.io/badge/MySQL-005C84?style=for-the-badge&logo=mysql&logoColor=white" alt="MySQL" />
    <img src="https://img.shields.io/badge/Bootstrap_5-7952B3?style=for-the-badge&logo=bootstrap&logoColor=white" alt="Bootstrap" />
    <img src="https://img.shields.io/badge/JavaScript-323330?style=for-the-badge&logo=javascript&logoColor=F7DF1E" alt="JavaScript" />
    <img src="https://img.shields.io/badge/Status-Concluído-success?style=for-the-badge" alt="Status" />
  </p>
</div>

<br>

## 📌 Sobre o Projeto
Este sistema foi construído para resolver um problema real: a perda de informações e o controle manual do pagamento de mensalistas em uma instituição de caridade. A aplicação garante previsibilidade financeira para a instituição, permitindo que os voluntários foquem no que realmente importa: **o auxílio espiritual e social**.

---

## 🚀 Funcionalidades Principais

### 👥 Gestão de Membros (Mensalistas)
- [x] Cadastro completo de fiéis (Nome, Contato, Data de Ingresso).
- [x] Controle de periodicidade de contribuição (Mensal, Bimestral, Semestral, Anual).
- [x] Cálculo dinâmico e automático das datas de vencimento.

### 💰 Gestão Financeira e Auditoria
- [x] Registro de pagamentos com categorização de meios (PIX, Dinheiro, Cartão).
- [x] **Relatório de Fechamento de Caixa:** Geração de demonstrativos formatados para impressão (A4) com separação de totais.
- [x] **Immutable Ledger (Auditoria Segura):** Erros de lançamento não são apagados, são retificados. O sistema mantém o histórico do erro riscado e registra a justificativa da correção, garantindo 100% de transparência contábil.

### 📱 Comunicação Automatizada
- [x] Notificações automáticas de vencimento baseadas em templates personalizáveis (ex: `Olá {{nome}}, sua mensalidade vence em {{prazo}}`).
- [x] Integração para envio rápido via **WhatsApp** (Redirecionamento com texto URL-encoded).
- [x] Suporte arquitetural para gateways de disparo de **SMS** via cURL.
- [x] Simulação de disparo de E-mails.

### 🛡️ Segurança e Privacidade
- [x] **Conformidade com a LGPD:** Checkbox de consentimento explícito para armazenamento de dados e contato.
- [x] Proteção contra **SQL Injection** através de Prepared Statements (PDO).
- [x] Autenticação segura com senhas criptografadas (`password_hash`).
- [x] Hierarquia de acessos: Administradores comuns não podem alterar credenciais do "Admin Mestre".

---

## 💻 Telas do Sistema

> **Nota:** Imagens demonstrativas da interface.

<div align="center">
  <img src="/docs/1.png">
    <img src="/docs/2.png">
    <img src="/docs/3.png">
    <img src="/docs/4.png">
    <img src="/docs/5.png">
    <img src="/docs/6.png">
    <img src="/docs/7.png">
</div>

---

## 🛠️ Como Executar o Projeto Localmente

Para rodar esta aplicação na sua máquina, você precisará de um servidor local como o **XAMPP** ou **Laragon** instalado.

1. **Clone o repositório:**
   ```bash
   git clone [https://github.com/viniciusbarauna/administracao_espirita]
   Configuração do Banco de Dados:

2. Abra o phpMyAdmin (ou seu gerenciador preferido).

Crie um banco de dados chamado lar_espirita_db (ou administracao_espirita).

Importe o arquivo database.sql (disponível na raiz do projeto) para criar as tabelas estruturadas.

3. Configuração do Sistema:

Acesse o arquivo config.php e ajuste as credenciais do seu banco de dados (usuário, senha e nome do DB).

4. Acesso:

Inicie o Apache e o MySQL no seu servidor local.

Acesse no navegador: http://localhost/nome-da-pasta-do-projeto

Login padrão de teste: (Adicione aqui o e-mail e a senha do admin mestre gerado para testes).

👨‍💻 Autor
Criado e desenvolvido por Vinícius Baraúna Lins, estudante de Análise e Desenvolvimento de Sistemas da faculdade Descomplica. Feito com dedicação para unir tecnologia e impacto social.
<img src="https://img.shields.io/badge/PHP-777BB4?style=for-the-badge&logo=php&logoColor=white"/>

<p align="center">Feito com ❤️ em Recife/PE.</p>
