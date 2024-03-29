#!/usr/bin/perl
#
#    extract-p7m.pl
#    Copyright (C) 2011  Nicola Inchingolo
#
#    This program is free software: you can redistribute it and/or modify
#    it under the terms of the GNU General Public License as published by
#    the Free Software Foundation, either version 3 of the License, or
#    (at your option) any later version.
#
#    This program is distributed in the hope that it will be useful,
#    but WITHOUT ANY WARRANTY; without even the implied warranty of
#    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
#    GNU General Public License for more details.
#
#    You should have received a copy of the GNU General Public License
#    along with this program.  If not, see <http://www.gnu.org/licenses/>.

use strict;
use warnings;

##COSTANTI/PARAMETRI
my $OPENSSL_COMMAND = "openssl";

my $current_dir;
my $messages = [];

foreach (1..5) {
    opendir ($current_dir, ".") or die $!;

    while (my $file = readdir($current_dir)) {
        my $estensione = substr ($file,-4,4);
        if ((lc($estensione)) eq '.p7m') {
            openssl_decrypt_file($file);
        }
    }

    closedir $current_dir;
}

printReport();

<STDIN>; #premere un tasto per continuare

sub openssl_decrypt_file {
    my $file_input_name = shift;
    my $file_output_name = substr $file_input_name, 0, -4; #tolgo l'estensione .p7m finale

    # questo comando l'ho preso da un post di Luca Regoli, che ringrazio
    # comando: openssl smime -decrypt -verify -inform DER -in "IAVCTD4_RilievoEssenze.dxf.p7m" -noverify -out "IAVCTD4_RilievoEssenze.dxf"
    my @args = ($OPENSSL_COMMAND, "smime", "-decrypt", "-verify", "-inform", "DER", "-in", $file_input_name, "-noverify", "-out", $file_output_name);

    ##print "@args\n";

    print "\nEstrazione file $file_input_name\n";
    my $exit_code = system(@args);

    # se va a buon fine devo cancellare l'originale, altrimenti devo cancellare l'output se e' stato creato
    if ($exit_code eq 0) {
        # cancellare il file di input
        push @$messages, "[$file_input_name] >OK<";
        unlink $file_input_name;
    }
    else {
        # cancello il file di output e rinomino l'input per non farlo riprocessare
        if (-e $file_output_name) {
            push @$messages, "[$file_input_name] >>>>> ERRORE NELL'ESTRAZIONE <<<<<";
            unlink $file_output_name;
        }

        rename $file_input_name, "ERRORE_${file_input_name}_ERRORE";
    }
}

sub printReport {
    print "\n\n";
    print "+-------------------------------------------------------+\n\nFile elaborati:\n\n";
    print (join "\n", @$messages);
    print "\n\n+-------------------------------------------------------+\n";
    print "|  ESTRAZIONE COMPLETATA (premere un tasto per uscire)  |\n";
    print "+-------------------------------------------------------+\n\n";
}
