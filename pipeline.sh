#!/bin/bash
# Automatización de CI/CD mediante Polling para entornos locales XAMPP

# PASO 0: Configurar la ruta del proyecto (Asegúrate de cambiar "nombre_de_tu_proyecto" por el nombre real de tu carpeta)
cd /c/xampp/htdocs/nombre_de_tu_proyecto

echo "Iniciando Pipeline de CI/CD... Escuchando cambios en la rama 'pruebas'."

# Bucle infinito para revisar cambios sin intervención humana
while true; do
    # PASO 1: Detectar cambios en la rama 'pruebas'
    # Actualizamos la información del remoto sin modificar los archivos locales aún
    git fetch origin pruebas >/dev/null 2>&1

    # Comparamos el último commit de nuestra copia local con el remoto de 'pruebas'
    LOCAL=$(git rev-parse HEAD)
    REMOTE=$(git rev-parse origin/pruebas)

    if [ "$LOCAL" != "$REMOTE" ]; then
        echo "[$(date +'%T')] Nuevos cambios detectados en origin/pruebas."
        
        # PASO 2: Compilar/Mover los nuevos cambios
        # Descargamos los cambios de la rama de pruebas y los fusionamos
        git pull origin pruebas
        
        # PASO 3: Poner a disposición del cliente
        echo "[$(date +'%T')] Despliegue exitoso. Cambios de la rama 'pruebas' disponibles en localhost."
    fi

    # Esperar 60 segundos antes de volver a consultar el repositorio
    sleep 60
done