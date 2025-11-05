<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sugerir Nombre de Usuario - Valora</title>
    <link rel="stylesheet" href="/assets/css/styles.css">
    <style>
        .ai-container {
            max-width: 600px;
            margin: 50px auto;
            padding: 30px;
            background: white;
            border-radius: 20px;
            box-shadow: 0 8px 32px rgba(0, 0, 0, 0.1);
        }
        
        .ai-header {
            text-align: center;
            margin-bottom: 30px;
        }
        
        .ai-header h2 {
            color: #882A57;
            font-family: 'Poppins', sans-serif;
            font-weight: 600;
            margin-bottom: 10px;
        }
        
        .ai-header p {
            color: #666;
            font-size: 14px;
            margin: 0;
        }
        
        .input-group {
            display: flex;
            gap: 12px;
            margin-bottom: 25px;
        }
        
        .input-group input {
            flex: 1;
            padding: 14px 16px;
            border: 1px solid #ee6f92;
            border-radius: 12px;
            font-size: 16px;
            font-family: 'Poppins', sans-serif;
            background-color: #fafafa;
            transition: all 0.3s ease;
        }
        
        .input-group input:focus {
            outline: none;
            border-color: #882A57;
            background-color: white;
            box-shadow: 0 0 0 3px rgba(136, 42, 87, 0.1);
        }
        
        .btn-ai {
            padding: 14px 24px;
            background: linear-gradient(135deg, #ee6f92, #882A57);
            color: white;
            border: none;
            border-radius: 12px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            font-family: 'Poppins', sans-serif;
        }
        
        .btn-ai:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 25px rgba(238, 111, 146, 0.3);
        }
        
        .btn-ai:disabled {
            background: #ccc;
            transform: none;
            box-shadow: none;
            cursor: not-allowed;
        }
        
        .suggestions-list {
            list-style: none;
            padding: 0;
            margin: 0;
        }
        
        .suggestions-list li {
            padding: 15px 20px;
            margin-bottom: 8px;
            background: #f8f9fa;
            border: 1px solid #e9ecef;
            border-radius: 12px;
            cursor: pointer;
            transition: all 0.3s ease;
            font-family: 'Poppins', sans-serif;
            display: flex;
            align-items: center;
            justify-content: space-between;
        }
        
        .suggestions-list li:hover {
            background: #fff;
            border-color: #ee6f92;
            transform: translateY(-1px);
            box-shadow: 0 4px 15px rgba(238, 111, 146, 0.1);
        }
        
        .suggestions-list li.loading {
            text-align: center;
            color: #882A57;
            font-style: italic;
            cursor: default;
        }
        
        .suggestions-list li.error {
            background: #fee;
            border-color: #fcc;
            color: #c33;
            cursor: default;
        }
        
        .username-text {
            font-weight: 500;
            color: #333;
        }
        
        .select-badge {
            background: #ee6f92;
            color: white;
            padding: 4px 8px;
            border-radius: 6px;
            font-size: 12px;
            font-weight: 500;
        }
        
        .back-link {
            display: inline-block;
            margin-top: 20px;
            color: #882A57;
            text-decoration: none;
            font-weight: 500;
            padding: 10px 20px;
            border: 1px solid #ee6f92;
            border-radius: 8px;
            transition: all 0.3s ease;
        }
        
        .back-link:hover {
            background: #ee6f92;
            color: white;
            text-decoration: none;
        }
    </style>
</head>
<body>
    <div class="ai-container">
        <div class="ai-header">
            <img src="/assets/images/logos/logo_valora.png" alt="Valora Logo" style="height: 60px; margin-bottom: 15px;">
            <h2>✨ Sugerir nombre de usuario con IA</h2>
            <p>Describe tu estilo o personalidad para recibir sugerencias únicas y elegantes</p>
        </div>

        <div class="input-group">
            <input type="text" id="userPrompt" placeholder="Ej: elegante, internacional, creativa, misteriosa..." maxlength="100">
            <button id="btnGenerarNombre" class="btn-ai">🤖 Sugerir con IA</button>
        </div>

        <ul id="sugerenciasIA" class="suggestions-list"></ul>
        
        <div style="text-align: center;">
            <a href="/views/register.php" class="back-link">← Volver al registro</a>
        </div>
    </div>

    <script>
        document.getElementById("btnGenerarNombre").addEventListener("click", async () => {
            const prompt = document.getElementById("userPrompt").value.trim();
            const list = document.getElementById("sugerenciasIA");
            const btn = document.getElementById("btnGenerarNombre");

            if (!prompt) {
                alert("Por favor escribe al menos una palabra clave sobre tu estilo.");
                return;
            }

            // Estado de carga
            btn.disabled = true;
            btn.innerHTML = "🔄 Generando...";
            list.innerHTML = '<li class="loading">🧠 La IA está creando sugerencias personalizadas...</li>';

            try {
                const response = await fetch("/controllers/usernameGenerator.php", {
                    method: "POST",
                    headers: {"Content-Type": "application/x-www-form-urlencoded"},
                    body: "prompt=" + encodeURIComponent(prompt)
                });

                const data = await response.json();
                list.innerHTML = "";

                if (data.error) {
                    throw new Error(data.error);
                }

                // Procesar respuesta de OpenAI
                const content = data.choices[0].message.content;
                const suggestions = content.split(/\d+\.\s*/).filter(name => name.trim());

                if (suggestions.length === 0) {
                    throw new Error("No se generaron sugerencias válidas");
                }

                // Mostrar sugerencias
                suggestions.forEach((name, index) => {
                    const cleanName = name.trim().replace(/[^\w\s]/g, '');
                    if (cleanName) {
                        const li = document.createElement("li");
                        li.innerHTML = `
                            <span class="username-text">${cleanName}</span>
                            <span class="select-badge">Seleccionar</span>
                        `;
                        
                        li.onclick = () => {
                            // Aquí podrías integrar con el formulario de registro
                            if (confirm(`¿Usar "${cleanName}" como nombre de usuario?`)) {
                                // Redirigir al registro con el nombre preseleccionado
                                window.location.href = `/views/register.php?suggested_username=${encodeURIComponent(cleanName)}`;
                            }
                        };
                        
                        list.appendChild(li);
                    }
                });

                if (list.children.length === 0) {
                    throw new Error("No se pudieron procesar las sugerencias");
                }

            } catch (error) {
                console.error("Error:", error);
                list.innerHTML = `
                    <li class="error">
                        ❌ Error: ${error.message}
                        <br><small>Verifica tu conexión e inténtalo nuevamente</small>
                    </li>
                `;
            } finally {
                // Restaurar botón
                btn.disabled = false;
                btn.innerHTML = "🤖 Sugerir con IA";
            }
        });

        // Permitir envío con Enter
        document.getElementById("userPrompt").addEventListener("keypress", (e) => {
            if (e.key === "Enter") {
                document.getElementById("btnGenerarNombre").click();
            }
        });
    </script>
</body>
</html>