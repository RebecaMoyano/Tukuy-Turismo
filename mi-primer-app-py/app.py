# -- App presupuesto personal --
#1. Se pide el presupuesto inciial 
presupuesto = float(input("Ingrese su presupuesto inicial:"))

#2. Pedimmos un gasto
gasto = float(input("Ahora, ingrese sus gastos totales:"))

#3. Calculamos el restante 
restante = presupuesto - gasto

# 4. Mostramos el resultado
print("\n--- RESUMEN ---")
print(f"Presupuesto inicial: ${presupuesto}")
print(f"Gasto realizado: (-${gasto})")
print(f"Dinero restante: ${restante}")