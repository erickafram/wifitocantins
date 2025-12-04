Este arquivo salva todas as configurações, inclusive senhas e licenças.
/system backup save name=config_backup

ARQVUIDO RSC
/export file=config_export

RESTAURAR BACKUP COMPLETO (.backup)
/system backup load name=config_backup.backup


RESTAURAR EXPORTAÇÃO (.rsc)
/import file-name=config_export.rsc
