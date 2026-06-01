#!/bin/bash
# Automatización de CI/CD mediante Polling para entornos locales XAMPP

# PASO 0: Configurar la ruta del proyecto (Asegúrate de cambiar "nombre_de_tu_proyecto")
cd /c/xampp/htdocs/nombre_de_tu_proyecto

echo "Iniciando Pipeline de CI/CD... Escuchando cambios en la rama 'pruebas'."

# Bucle infinito para revisar cambios
while true; do
    # PASO 1: Detectar cambios en la rama 'pruebas'
    git fetch origin pruebas >/dev/null 2>&1

    # Comparamos el último commit de nuestra copia local con el remoto de 'pruebas'
    LOCAL=$(git rev-parse HEAD)
    REMOTE=$(git rev-parse origin/pruebas)

    if [ "$LOCAL" != "$REMOTE" ]; then
        echo -e "\n[$(date +'%T')] ¡Atención! Nuevos cambios detectados en origin/pruebas."
        
        # Preguntar al usuario mediante la terminal
        read -p "¿Deseas descargar e instalar la actualización en este momento? (s/no =precione cualquier otra teclado): " respuesta
        
        # Evaluar la respuesta (acepta 's' minúscula o 'S' mayúscula)
        if [[ "$respuesta" == "s" || "$respuesta" == "S" ]]; then
            echo "Descargando cambios..."
            # PASO 2: Compilar/Mover los nuevos cambios
            git pull origin pruebas
            
            # PASO 3: Poner a disposición del cliente
            echo "[$(date +'%T')] Despliegue exitoso. Cambios disponibles en localhost."
        else
            # Si el usuario escribe 'n' o cualquier otra cosa
            echo "[$(date +'%T')] Actualización pospuesta. Se mantendrá la versión actual."
        fi
    fi

    # Esperar 60 segundos antes de volver a consultar el repositorio
    sleep 60
done