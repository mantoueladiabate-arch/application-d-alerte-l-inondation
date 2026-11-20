import 'package:flutter/material.dart';

import 'screens/report_screen.dart';

void main() {
  runApp(const AlerteInondationApp());
}

class AlerteInondationApp extends StatelessWidget {
  const AlerteInondationApp({super.key});

  @override
  Widget build(BuildContext context) {
    return MaterialApp(
      title: 'Alerte Inondation',
      debugShowCheckedModeBanner: false,
      theme: ThemeData(colorScheme: ColorScheme.fromSeed(seedColor: Colors.blue)),
      home: const ReportScreen(),
    );
  }
}
