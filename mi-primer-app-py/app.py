# App presupuesto personal
#1. Se pide el presupuesto incial 
presupuesto = float(input("Ingrese su presupuesto inicial:"))
gastos = [] 
respuesta = ""
#2. Se agregan los posibles gastos
while presupuesto > 0:
    respuesta =  input("Quieres agregar un gasto?")
    if respuesta == "si" or respuesta == "s":
        nombre_gasto = input("Gasto: ")
        cantidad = float(input("Valor: "))
        gastos.append({"nombre_gasto":nombre_gasto, "valor":cantidad})
        print("Gasto registrado con éxito.")
    else:
        #3. Se enumeran los gastos totales y se obtiene el presupuesto restante
        print("---Tus gastos hasta ahora---")
        for gasto in gastos:
            print(f"Nombre: {gasto['nombre_gasto']} --- Cantidad: {gasto['valor']} $")
            presupuesto -= gasto['valor']
        break 
#4. Fin programa
print("\n--- RESUMEN ---")
print(f"Presupuesto final: ${presupuesto} $")